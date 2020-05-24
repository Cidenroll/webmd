<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/24/2020
 * Time: 12:25 PM
 */

namespace App\Controller;


use App\Entity\User;
use App\Entity\UserFile;
use App\Parser\AnalysisParser;
use App\Parser\MedicalReportParser;
use App\Repository\RelationsPd2Repository;
use App\Repository\UserFileRepository;
use App\Services\UploaderHelper;
use Aws\S3\S3Client;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Security\Core\Security;


class OCRController extends AbstractController
{

    private $apiKey;

    /**
     * @var Security
     */
    private $security;
    /**
     * @var UserFile
     */
    private $userFileRepository;

    /**
     * @var bool
     */
    private $usedPDFParser;
    /**
     * @var RelationsPd2Repository
     */
    private $pd2Repository;

    /**
     * @var S3ClientAlias|S3Client
     */
    private $s3Client;


    /**
     * OCRController constructor.
     * @param S3ClientAlias $s3Client
     * @param FilesystemInterface $publicUploadFileSystem
     * @param Security $security
     * @param UserFileRepository $userFileRepository
     * @param RelationsPd2Repository $pd2Repository
     */
    public function __construct(S3Client $s3Client, Security $security, UserFileRepository $userFileRepository, RelationsPd2Repository $pd2Repository)
    {
        $this->security = $security;
        $this->userFileRepository = $userFileRepository;
        $this->usedPDFParser = false;
        $this->pd2Repository = $pd2Repository;
        $this->s3Client = $s3Client;
    }

    /**
     * @Route("/medform/{id}", name="medform")
     * @param $id
     * @param UploaderHelper $uploaderHelper
     * @return RedirectResponse|Response
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function getFileAndOcr($id, UploaderHelper $uploaderHelper)
    {
        // check id
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        /** @var UserFile $userFileObj */
        $userFileObj = $this->userFileRepository->find($id);

        // if the user is trying to alter the above id above, atleast make him look just into his own; else, return 404
        if ($currentUser->getId() != $userFileObj->getUserId()->getId() && $userFileObj->getUserId()) {
            return $this->redirect($this->generateUrl('notFound'));
        }

        $this->apiKey = $this->getParameter('ocr_apikey');

        $userFile = $this->userFileRepository->find($id);
        $userFilePath = sprintf("%s/%s",$this->getParameter('pdf_directory'), $userFile->getFileName());

//        $stream = fopen($uploaderHelper->getPublicPath($userFile->getImagePath()), 'r');
        $stream = file_get_contents($uploaderHelper->getPublicPath($userFile->getImagePath()));

        $ocrRawOutput = [];
        try {
            $ocrRawOutput = $this->ocrImageParse(null, $stream);
        }
        catch (\Exception $exception) {
            $ocrRawOutput = ['text' => "", 'details' => []];
        }

//        if (is_resource($stream)) {
//            fclose($stream);
//        }

        // IF the document is of type 'Annual checkup'
        /** @var UserFile $fileObj */
        $fileObj = $this->userFileRepository->findDocTypeByFileId($id)[0];

        if ($fileObj->getDocType() === 'Annual checkup') {

            if ($this->usedPDFParser) {
                $analysisParserObj = new AnalysisParser($ocrRawOutput['text'], $ocrRawOutput['details']);
                $analysisParserObj->process();
            }else {
                $analysisParserObj = new AnalysisParser($ocrRawOutput['text'], $ocrRawOutput['details']);
                $analysisParserObj->process();
            }

            return $this->render('medform/annualcheck.html.twig',[
                    "userFileId"        =>  $id,
                    "patientInfo" => $analysisParserObj->getAllData(),
                    "patientDoctors"    =>  $this->pd2Repository->findAllRelationsToDoctorsByPatientId($currentUser->getId())
                ]
            );
        }
        else {

            if ($this->usedPDFParser) {
                $analysisParserObj = new MedicalReportParser($ocrRawOutput['text'], $ocrRawOutput['details']);
                $analysisParserObj->process();
            }else {
                $analysisParserObj = new MedicalReportParser($ocrRawOutput['text'], $ocrRawOutput['details']);
                $analysisParserObj->process();
            }

            return $this->render('medform/annualcheck.html.twig',[
                    "userFileId"        =>  $id,
                    "patientInfo" => $analysisParserObj->getAllData(),
                    "patientDoctors"    =>  $this->pd2Repository->findAllRelationsToDoctorsByPatientId($currentUser->getId())
                ]
            );
        }


    }

    /**
     * @Route("/medform/{id}/viewComment", name="viewComment")
     * @param $id
     * @return RedirectResponse|Response
     */
    public function viewMedicalComment($id)
    {
        // check id
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        /** @var UserFile $userFileObj */
        $userFileObj = $this->userFileRepository->find($id);

        // if the user is trying to alter the above id above, atleast make him look just into his own; else, return 404
        if ($currentUser->getId() != $userFileObj->getUserId()->getId()) {
            return $this->redirect($this->generateUrl('notFound'));
        }

        return $this->render('medAccount/viewcomment.html.twig', [
            'fileName'  =>  $userFileObj->getFileName(),
            'comment'   =>  $userFileObj->getComment()
        ]);

    }


    /**
     * @param String $filePath
     * @param null $fileContent
     * @return array
     * @throws \Exception
     */
    private function ocrImageParse(String $filePath= null, $fileContent = null): array
    {
        $parser = new Parser();
        if ($filePath) {
            $pdf = $parser->parseFile($filePath);
        }
        else {
            $pdf = $parser->parseContent($fileContent);
        }

        if (strlen($pdf->getText()) < 150) {
            // Create a TMP file of the image with PNG format
            $fileName = uniqid(true, true).'.png';
            // Get the path of the temporal image
            $outputImagePath = $this->getParameter('image_directory', $fileName);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,"http://api.ocr.space/parse/image");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'apikey'    =>  $this->apiKey,
                    'base64image'=>  'data:application/pdf;base64,'.base64_encode(file_get_contents($filePath)),
                    'filetype'  =>  'PDF',
                    'scale' =>  'false',
                    'OCREngine' =>  '2',
                ]);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            curl_close($ch);

            $textEngine2 = json_decode($serverOutput, true);
            $parsedTextEngine2 = $textEngine2['ParsedResults'][0]['ParsedText'];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,"http://api.ocr.space/parse/image");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'apikey'    =>  $this->apiKey,
                'base64image'=>  'data:application/pdf;base64,'.base64_encode(file_get_contents($filePath)),
                'filetype'  =>  'PDF',
                'scale' =>  'false',
                'OCREngine' =>  '1',
            ]);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            curl_close($ch);

            $textEngine1 = json_decode($serverOutput, true);
            $parsedTextEngine1 = $textEngine1['ParsedResults'][0]['ParsedText'];

            $mergeEngines = implode('\n^^^^^%%^^^^^^\n', [$parsedTextEngine2,$parsedTextEngine1]);

            $details = $pdf->getDetails();
            $text = $mergeEngines;
        }
        else {

            $this->usedPDFParser = true;
            $details = $pdf->getDetails();
            $text = $pdf->getText();

        }

        return [
            'details'   =>  $details,
            'text'  =>  $text
        ];

    }






}
<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/24/2020
 * Time: 12:25 PM
 */

namespace App\Controller;


use App\Entity\ProcessedFiles;
use App\Entity\User;
use App\Entity\UserFile;
use App\Parser\AnalysisParser;
use App\Parser\MedicalReportParser;
use App\Repository\RelationsPd2Repository;
use App\Repository\UserFileRepository;
use App\Services\LogAnalyticsService;
use App\Services\UploaderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;


class OCRController extends AbstractController
{

    private const USERFILE_ID       = 'userFileId';
    private const PATIENT_INFO      = 'patientInfo';
    private const PATIENT_DOCTORS   = 'patientDoctors';
    private const LATEST_COMMENTED  = 'lastCommentingDoctor';

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
     * OCRController constructor.
     * @param Security $security
     * @param UserFileRepository $userFileRepository
     * @param RelationsPd2Repository $pd2Repository
     */
    public function __construct(Security $security, UserFileRepository $userFileRepository, RelationsPd2Repository $pd2Repository)
    {
        $this->security = $security;
        $this->userFileRepository = $userFileRepository;
        $this->usedPDFParser = false;
        $this->pd2Repository = $pd2Repository;
    }

    /**
     * @Route("/medform/{id}", name="medform")
     * @param $id
     * @param UploaderHelper $uploaderHelper
     * @param LogAnalyticsService $analytics
     * @return RedirectResponse|Response
     */
    public function getFileAndOcr($id, UploaderHelper $uploaderHelper, LogAnalyticsService $analytics)
    {
        // check id
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        /** @var UserFile $userFileObj */
        $userFileObj = $this->userFileRepository->find($id);

        if (!$currentUser) {
            return $this->redirect($this->generateUrl('homepage'));
        }
        // if the user is trying to alter the above id above, atleast make him look just into his own; else, return 404
        if ($userFileObj->getUserId() && $currentUser->getId() != $userFileObj->getUserId()->getId()) {
            return $this->redirect($this->generateUrl('notFound'));
        }

        /*****************************************************
        // search for the processed file in the db, if found, skip the OCR part
        /** @var ProcessedFiles $processedFile */
        $processedFiles = $this->getDoctrine()->getRepository(ProcessedFiles::class)->findProcessedFileByFileID($id);
        /** @var ProcessedFiles $processedFile */
        if ($processedFiles && $processedFile = $processedFiles[0]) {
            $content = json_decode($processedFile->getContent(), true);


            $contentObj = new \stdClass();
            $contentObj->sex = $content['sex'];
            $contentObj->cnp = $content['cnp'];
            $contentObj->age = $content['age'];
            $contentObj->institute = $content['institution'];
            $contentObj->dates = $content['dates'];
            $contentObj->diagnostic = $content['resultSummary'];
            $contentObj->highestVals = $content['diagnostic']?:null;
            $contentObj->selectedDoctor = $content['docId']?:0;
            $contentObj->selectedDoctorName = '';
            if ($content['docId']){
                $contentObj->selectedDoctorName = $this->getDoctrine()->getRepository(User::class)->find($content['docId'])->getEmail();
            }

            $lastCommentingDoctor =  $lastCommentDocEmail = null;
            if ($this->userFileRepository->find($id)->getLatestCommentedDoctorID()) {
                $lastCommentingDoctor = $this->getDoctrine()->getRepository(User::class)->find($this->userFileRepository->find($id)->getLatestCommentedDoctorID());
            }

            if ($lastCommentingDoctor) {
                $lastCommentDocEmail = $lastCommentingDoctor->getEmail();
            }

            return $this->render('medform/annualcheck.html.twig',[
                    self::USERFILE_ID        => $id,
                    self::PATIENT_INFO       => $contentObj,
                    self::PATIENT_DOCTORS    => $this->pd2Repository->findAllRelationsToDoctorsByPatientId($currentUser->getId()),
                    self::LATEST_COMMENTED   => $lastCommentDocEmail
                ]
            );
        }
        /*****************************************************************
         *
         */

        $this->apiKey = $this->getParameter('ocr_apikey');
        $userFile = $this->userFileRepository->find($id);
        $stream = file_get_contents($uploaderHelper->getPublicPath($userFile->getImagePath()));

        $ocrRawOutput = [];
        try {
            $ocrRawOutput = $this->ocrImageParse(null, $stream);
        }
        catch (\Exception $exception) {
            $ocrRawOutput = ['text' => [], 'details' => []];
        }

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
                    self::USERFILE_ID       =>  $id,
                    self::PATIENT_INFO      => $analysisParserObj->getAllData(),
                    self::PATIENT_DOCTORS   =>  $this->pd2Repository->findAllRelationsToDoctorsByPatientId($currentUser->getId())
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
                    self::USERFILE_ID       =>  $id,
                    self::PATIENT_INFO      => $analysisParserObj->getAllData(),
                    self::PATIENT_DOCTORS   =>  $this->pd2Repository->findAllRelationsToDoctorsByPatientId($currentUser->getId())
                ]
            );
        }
    }

    /**
     * @Route("/medform/{id}/viewComment", name="viewComment")
     * @param $id
     * @param LogAnalyticsService $analytics
     * @return RedirectResponse|Response
     */
    public function viewMedicalComment($id, LogAnalyticsService $analytics)
    {
        // check id
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        /** @var UserFile $userFileObj */
        $userFileObj = $this->userFileRepository->find($id);

        if (!$currentUser) {
            return $this->redirect($this->generateUrl('homepage'));
        }

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

        $mergeEngines = [];
        $mergeEngines['SMALOT'] = $pdf->getText();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://api.ocr.space/parse/image");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'apikey'    =>  $this->apiKey,
                'base64image'=>  'data:application/pdf;base64,'.base64_encode($fileContent),
                'filetype'  =>  'PDF',
                'scale' =>  'false',
                'OCREngine' =>  '2',
            ]);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $serverOutput = curl_exec($ch);
        curl_close($ch);

        $textEngine2 = json_decode($serverOutput, true);
        $parsedTextEngine2 = $textEngine2['ParsedResults'][0]['ParsedText'];

        $mergeEngines['OCRSPACEV2'] = $parsedTextEngine2;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://api.ocr.space/parse/image");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'apikey'    =>  $this->apiKey,
            'base64image'=>  'data:application/pdf;base64,'.base64_encode($fileContent),
            'filetype'  =>  'PDF',
            'scale' =>  'false',
            'OCREngine' =>  '1',
        ]);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $serverOutput = curl_exec($ch);
        curl_close($ch);

        $textEngine1 = json_decode($serverOutput, true);
        $parsedTextEngine1 = $textEngine1['ParsedResults'][0]['ParsedText'];
        $mergeEngines['OCRSPACEV1'] = $parsedTextEngine1;

        $this->usedPDFParser = true;
        $details = $pdf->getDetails();

        return [
            'details'   =>  $details,
            'text'  =>  $mergeEngines
        ];
    }
}
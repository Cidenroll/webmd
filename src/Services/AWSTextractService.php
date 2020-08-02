<?php


namespace App\Services;


use Aws\Textract\TextractClient;

class AWSTextractService
{

    private $accessKey = '';
    private $secret='';

    public function __construct($accessKey, $secret)
    {
        $this->accessKey = $accessKey;
        $this->secret = $secret;
    }

    public function init()
    {
        $client = new TextractClient([
            'region' => 'eu-central-1',
            'version' => '2018-06-27',
            'credentials' => [
                'key'    => $this->accessKey,
                'secret' => $this->secret
            ]
        ]);

        $filename = "https://msing-sdm.s3.eu-central-1.amazonaws.com/pdfs%2Fconfirmed%2F11_7.+Codeception+%281%29_15ecfab8abcb6d6.39381295.pdf";
        $file = fopen($filename, "rb");
        $contents = fread($file);
        fclose($file);
        $options = [
            'Document' => [
                'Bytes' => $contents
            ],
            'FeatureTypes' => ['FORMS'], // REQUIRED
        ];
        $result = $client->analyzeDocument($options);
        dd($result);
    }


}
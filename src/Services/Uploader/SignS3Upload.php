<?php

namespace A17\CmsToolkit\Services\Uploader;

use A17\CmsToolkit\Services\Uploader\SignS3UploadListener;

class SignS3Upload
{
    private $bucket;

    private $secret;

    private $endpoint;

    public function __construct()
    {
        $this->bucket = config('filesystems.disks.s3.bucket');
        $this->secret = config('filesystems.disks.s3.secret');
        $this->endpoint = s3Endpoint();
    }

    public function fromPolicy($policy, SignS3UploadListener $listener)
    {
        $policyObject = json_decode($policy, true);
        $policyJson = json_encode($policyObject);
        $policyHeaders = $policyObject["headers"] ?? null;

        if ($policyHeaders) {
            $signedPolicy = $this->signChunkedRequest($policyHeaders, $listener);
        } else {
            $signedPolicy = $this->signPolicy($policyJson, $listener);
        }

        if ($signedPolicy) {
            return $listener->policyIsSigned($signedPolicy);
        }

        return $listener->policyIsNotValid();
    }

    private function signPolicy($policyJson, $listener)
    {
        $policyObject = json_decode($policyJson, true);

        if ($this->isValid($policyObject)) {
            $encodedPolicy = base64_encode($policyJson);
            $signedPolicy = array(
                'policy' => $encodedPolicy,
                'signature' => $this->signV4Policy($policyObject, $encodedPolicy),
            );
            return $signedPolicy;
        }

        return null;
    }

    private function isValid($policy)
    {
        $expectedMaxSize = null;
        $conditions = $policy["conditions"];
        $bucket = null;
        $parsedMaxSize = null;

        for ($i = 0; $i < count($conditions); ++$i) {
            $condition = $conditions[$i];
            if (isset($condition["bucket"])) {
                $bucket = $condition["bucket"];
            } else if (isset($condition[0]) && $condition[0] == "content-length-range") {
                $parsedMaxSize = $condition[2];
            }
        }

        return $bucket == $this->bucket && $parsedMaxSize == (string) $expectedMaxSize;
    }

    private function signV4Policy($policy, $encodedPolicy)
    {
        foreach ($policy["conditions"] as $condition) {
            if (isset($condition["x-amz-credential"])) {
                $credentialCondition = $condition["x-amz-credential"];
            }
        }

        $pattern = "/.+\/(.+)\\/(.+)\/s3\/aws4_request/";
        preg_match($pattern, $credentialCondition, $matches);

        $dateKey = hash_hmac('sha256', $matches[1], 'AWS4' . $this->secret, true);
        $dateRegionKey = hash_hmac('sha256', $matches[2], $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);

        return hash_hmac('sha256', $encodedPolicy, $signingKey);
    }

    private function signChunkedRequest($policyHeaders, $listener)
    {
        if (isValidChunckRequest($policyHeaders)) {
            $signedRequest = array('signature' => signV4ChunkRequest($policyHeaders));
            return $signedRequest;
        }

        return null;
    }

    private function isValidChunckRequest($policyHeaders)
    {
        $pattern = "/host:$this->endpoint/";
        preg_match($pattern, $policyHeaders, $matches);

        return count($matches) > 0;
    }

    private function signV4ChunkRequest($policyHeaders)
    {
        $pattern = "/.+\\n.+\\n(\\d+)\/(.+)\/s3\/aws4_request\\n(.+)/s";
        preg_match($pattern, $policyHeaders, $matches);
        $hashedCanonicalRequest = hash('sha256', $matches[3]);
        $stringToSign = preg_replace("/^(.+)\/s3\/aws4_request\\n.+$/s", '$1/s3/aws4_request' . "\n" . $hashedCanonicalRequest, $policyHeaders);
        $dateKey = hash_hmac('sha256', $matches[1], 'AWS4' . $this->secret, true);
        $dateRegionKey = hash_hmac('sha256', $matches[2], $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);
        return hash_hmac('sha256', $stringToSign, $signingKey);
    }

}

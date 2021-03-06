<?php

namespace Indiegogo;

/**
 * http://developer.indiegogo.com
 * @package Indiegogo
 */
class Client
{
    const DOMAIN = 'indiegogo.com';

    /**
     * Base URL for the Indiegogo Auth.
     *
     * @var string
     */
    public $authUrl = 'https://auth.' . self::DOMAIN;

    /**
     * Base URL for the Indiegogo Private API.
     *
     * @var string
     */
    public $privateApiUrl = 'https://www.' . self::DOMAIN . '/private_api';

    /**
     * Base URL for the Indiegogo API.
     *
     * @var string
     */
    public $apiUrl = 'https://api.' . self::DOMAIN;

    /**
     * API version
     *
     * @var int
     */
    public $apiVersion = 2;

    /**
     * The Indiegogo api token.
     * If API token is empty then use private api
     *
     * @var string
     */
    public $apiToken;

    /**
     * The Indiegogo app access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * HTTP status code returned by each request.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Client constructor.
     *
     * @param string $apiToken
     * @param string $accessToken
     * @throws \Exception
     */
    public function __construct($apiToken = null, $accessToken = null)
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('Indiegogo PHP API Client requires the CURL PHP extension');
        }
        $this->apiToken = $apiToken;
        $this->accessToken = $accessToken;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setApiToken($token)
    {
        $this->apiToken = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * http://developer.indiegogo.com/docs/credentials
     *
     * @return array
     */
    public function getCredentials()
    {
        return $this->call('GET', 'me');
    }

    /**
     * @param string $accountId The ID number assigned to the Indiegogo account.
     * @return array
     */
    public function getAccount($accountId)
    {
        return $this->call('GET', "accounts/{$accountId}");
    }

    /**
     * @param string $accountId The ID number assigned to the Indiegogo account.
     * @return array
     */
    public function getAccountContributions($accountId)
    {
        return $this->call('GET', "accounts/{$accountId}/contributions");
    }

    /**
     * @return array
     */
    public function getCampaigns()
    {
        return $this->call('GET', "campaigns");
    }

    /**
     * A paginated list of campaign comments.
     *
     * @param string $campaignId The ID of the Indiegogo campaign.
     * @return array
     */
    public function getCampaignComments($campaignId)
    {
        return $this->call('GET', "campaigns/{$campaignId}/comments");
    }

    /**
     * A paginated list of campaign contributions.
     *
     * @param string $campaignId The ID of the Indiegogo campaign.
     * @param string $email Use to filter by backer email.
     * @param string $status Use to filter by order status. Must be one of: captured, pending, in_fulfillment, fulfilled, refunded, unprocessable, on_hold.
     * @param string $perkId Use to filter by perk id.
     * @return array
     */
    public function getCampaignContributions($campaignId, $email = null, $status = null, $perkId = null)
    {
        return $this->call('GET', "campaigns/{$campaignId}/contributions", [
            'query' => [
                'email' => $email,
                'status' => $status,
                'perkId' => $perkId,
            ]
        ]);
    }

    /**
     * Get the current status of a batch order update job given an id.
     * Possible status responses are “queued”, “working”, “completed”, “failed”, or “killed”.
     *
     * @param string $campaignId The ID of the Indiegogo campaign.
     * @param string $jobId The previously obtained job ID.
     * @return array
     */
    public function getCampaignOrdersBatchStatus($campaignId, $jobId = null)
    {
        return $this->call('GET', "campaigns/{$campaignId}/orders/batch_status", [
            'query' => [
                'job_id' => $jobId
            ]
        ]);
    }

    /**
     * A paginated list of campaign perks.
     *
     * @param string $campaignId The ID of the Indiegogo campaign.
     * @return array
     */
    public function getCampaignPerks($campaignId)
    {
        return $this->call('GET', "campaigns/{$campaignId}/perks");
    }

    /**
     * A paginated list of campaign updates.
     *
     * @param string $campaignId The ID of the Indiegogo campaign.
     * @return array
     */
    public function getCampaignUpdates($campaignId)
    {
        return $this->call('GET', "campaigns/{$campaignId}/updates");
    }

    /**
     * Lists recommended campaigns.
     *
     * @return array
     */
    public function getCampaignRecommendations()
    {
        return $this->call('GET', 'campaigns/recommendations');
    }

    /**
     * A paginated list of favorite campaigns.
     * http://developer.indiegogo.com/docs/favorite-campaigns
     *
     * @return array
     */
    public function getFavorites()
    {
        return $this->call('GET', 'favorites');
    }

    /**
     * Search campaigns.
     * http://developer.indiegogo.com/docs/search
     *
     * @param array $params
     * @return array
     */
    public function getSearch($params = [])
    {
        return $this->call('GET', 'search/campaigns', [
            'query' => $params
        ]);
    }

    /**
     * Get the HTTP status code from the last response.
     *
     * @return int
     * @throws \Exception
     */
    public function getStatusCode()
    {
        if (empty($this->statusCode)) throw new \Exception('An HTTP status code has not been set. Make sure you ask for this AFTER you make a request to the API.');
        return (int)$this->statusCode;
    }

    /**
     * Authentication
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function auth($email, $password)
    {
        $ch = curl_init($this->authUrl . '/oauth/token');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'grant_type' => 'password',
                'credential_type' => 'email',
                'email' => $email,
                'password' => $password,
            ]
        );
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if (isset($result['access_token'])) {
            $this->setAccessToken($result['access_token']);
            return true;
        }
        return false;
    }


    // Private API Methods


    /**
     * @param $campaignId
     * @param array $params
     * @return array
     *
     * Example: {job_id: "..."}
     */
    public function contributionExport($campaignId, $params = [])
    {
        $this->checkPrivate();
        return $this->call('POST', "campaigns/{$campaignId}/contributions/export", $params);

    }

    /**
     * @param string $campaignId
     * @param array $params
     * @return array
     *
     * Example: {job_id: "..."}
     */
    public function contributionImport($campaignId, $params = [])
    {
        $this->checkPrivate();
        return $this->call('POST', "campaigns/{$campaignId}/contributions/import", $params);

    }

    /**
     * @param string $jobId
     * @return array
     *
     * Example: {time: 12345, status: "working"}
     * Example: {time: 99999, status: "completed", total: 99, download_url: "..."}
     */
    public function jobStatuses($jobId)
    {
        $this->checkPrivate();
        return $this->call('GET', "job_statuses/{$jobId}.json");

    }

    /**
     * Master call. It makes the requests to the API endpoints.
     *
     * @param string $httpMethod
     * @param string $endPoint
     * @param array $params
     * @return array
     */
    protected function call($httpMethod, $endPoint, $params = [])
    {
        $httpMethod = strtoupper($httpMethod);

        if (isset($params['query'])) {
            $query = (array)$params['query'];
            unset($params['query']);
        } else {
            $query = [];
        }

        $query['access_token'] = $this->accessToken;

        if ($this->apiToken) {
            $url = $this->apiUrl . '/' . $this->apiVersion . '/' . $endPoint . '.json';
            $query['api_token'] = $this->apiToken;
        } else {
            $url = $this->privateApiUrl . '/' . $endPoint;
        }

        $ch = curl_init($url . '?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0');
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for https
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

//        if (!isset($params['file'])) {
//            curl_setopt($ch, CURLOPT_HTTPHEADER, [
//                'Content-Type: application/json'
//            ]);
//        }

        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                isset($params['json']) ?
                    json_encode($params['json']) :
                    $params
            );
        }

        $response = curl_exec($ch);
        $this->statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * @throws \Exception
     */
    protected function checkPrivate()
    {
        if (!empty($this->apiToken)) {
            throw new \Exception('This method available only for private api.');
        }
    }
}
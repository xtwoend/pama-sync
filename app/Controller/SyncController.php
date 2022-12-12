<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Hole;
use App\Model\Site;
use App\Model\Downtime;
use App\Model\BinCapacity;
use App\Model\Pemeriksaan;
use Hyperf\Utils\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

#[Controller]
class SyncController
{
    #[Inject]
    protected ValidatorFactoryInterface $validator;

    #[Inject]
    protected ClientFactory $factory;

    protected $client;

    public function __construct() {
        $this->client = $this->factory->create();
    }

    #[RequestMapping(methods: "POST", path: "/sync/{truckId}")]
    public function receive($truckId, RequestInterface $request, ResponseInterface $response)
    {
        $this->validator->make($request->all(), [
            'meta.site' => 'required',
            'meta.pit' => 'required',
            'meta.ewacs_location' => 'required',
            'data' => 'required|array'
        ])->validate();

        $meta = $request->input('meta', null);
        $data = $request->input('data', []);

        $total = count($data);

        $site = Site::updateOrCreate([
            'site' => $meta['site'],
            'pit'  => $meta['pit'],
            'ewacs_location' => $meta['ewacs_location']
        ], [
            'burden' => $meta['burden'] ?? 0,
            'spacing' => $meta['spacing'] ?? 0
        ]);

        foreach($data as $row) {
            Hole::updateOrCreate([
                'site_id' => (string) $site->site,
                'hole_code' => $row['hole_code']
            ], [
                'truck_id' => $truckId,
                'deep' => $row['deep'],
                'volume' => $row['volume'],
                'condition' => $row['condition']
            ]);
        }

        $total = $site->holes()->count();
        
        $dataholes = $site->holes;

        return $response->json([
            'success'   => true, 
            'message'   => 'Success sync data',
            'total'     => $total,
            'data'      => $dataholes,
        ]);
    }

    #[RequestMapping(methods: "POST", path: "/send/{truckId}")]
    public function send($truckId, RequestInterface $request, ResponseInterface $response)
    {
        $action = $request->input('action');
        $url = $request->input('url');

        if(! in_array($action, ['charging', 'p2h', 'downtime', 'bincapacity']))
        {
            return $response->json([
                'success' => false,
                'message' => 'Action not found'
            ]);
        }

        $result = null;
        if($action == 'charging') {
            $data = Hole::all();

            $result = $this->client->post($url, [
                'headers' => [
                    'Document-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'truck_id' => $truckId,
                    'data' => $data,
                ],
            ]);
        }

        if($action == 'p2h') {

            $data = Pemeriksaan::all();
            $result = $this->client->post($url, [
                'headers' => [
                    'Document-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'truck_id' => $truckId,
                    'data' => $data,
                ],
            ]);
        }

        if($action == 'downtime') {
            $data = Downtime::all();
            $result = $this->client->post($url, [
                'headers' => [
                    'Document-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'truck_id' => $truckId,
                    'data' => $data,
                ],
            ]);
        }

        if($action == 'bincapacity') {
            $data = BinCapacity::all();
            $result = $this->client->post($url, [
                'headers' => [
                    'Document-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'truck_id' => $truckId,
                    'data' => $data,
                ],
            ]);
        }

        if(is_null($result) || $result->getStatusCode() != 200) {
            return $response->json([
                'success' => false,
                'message' => 'data not sending'
            ]);
        }

        $data = (string) $result->getBody();
        $json = Json::decode($data);

        return $response->json([
            'success' => true,
            'message' => 'success response',
            'response' => $json
        ]);
    }
}

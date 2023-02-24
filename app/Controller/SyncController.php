<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\P2h;
use App\Model\Rpm;
use Carbon\Carbon;
use App\Model\Site;
use App\Model\Loading;
use App\Model\Activity;
use App\Model\Charging;
use Hyperf\DbConnection\Db;
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
            'spacing' => $meta['spacing'] ?? 0,
            'plan_pf' => $meta['plan_pf'] ?? 0,
            'diameter' => $meta['diameter'] ?? 0,
        ]);

        foreach($data as $row) {
            Charging::updateOrCreate([
                'site_key' => $site->id,
                'hole_code' => $row['hole_code']
            ], [
                'truck_id' => (string) $truckId,
                'site_id' => $meta['site'],
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

        if(! in_array($action, ['charging', 'p2h', 'activity', 'loading']))
        {
            return $response->json([
                'success' => false,
                'message' => 'Action not found'
            ]);
        }

        $result = null;
        if($action == 'charging') {
            $data = Charging::where('truck_id', $truckId)->get();
            // $data = [];
            $result = $this->client->post($url, [
                'query' => [
                    'key' => env('KIDE_KEY'),
                ],
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

            $data = P2h::all();
            $result = $this->client->post($url, [
                'query' => [
                    'key' => env('KIDE_KEY'),
                ],
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

        if($action == 'activity') {
            $data = Activity::all();
            $result = $this->client->post($url, [
                'query' => [
                    'key' => env('KIDE_KEY'),
                ],
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

        if($action == 'loading') {
            $data = Loading::whereDate('datetime', Carbon::now()->format('Y-m-d'))->get();
            $result = $this->client->post($url, [
                'query' => [
                    'key' => env('KIDE_KEY'),
                ],
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

        if($action == 'motor') {
            $data = Rpm::all();
            $result = $this->client->post($url, [
                'query' => [
                    'key' => env('KIDE_KEY'),
                ],
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

        if(is_null($result) || ! in_array($result->getStatusCode(), [200, 201])) {
            return $response->json([
                'success' => false,
                'message' => 'data not sending'
            ]);
        }

        $data = (string) $result->getBody();
        $json = Json::decode($data);

        if($action == 'charging') {
            Db::table((new Charging)->getTable())->delete();
        }elseif($action == 'p2h') {
            Db::table((new P2h)->getTable())->delete();
        }elseif($action == 'activity') {
            Db::table((new Activity)->getTable())->delete();
        }elseif($action == 'loading') {
            // Db::table((new Loading)->getTable())->delete();
        }elseif($action == 'motor') {
            Db::table((new Rpm)->getTable())->delete();
        }

        return $response->json([
            'success' => true,
            'message' => 'success response',
            'response' => $json
        ]);
    }

    #[RequestMapping(methods: "POST", path: "/pama/dummy/url")]
    public function dummy(RequestInterface $request, ResponseInterface $response)
    {
        return $response->json($request->all());
    }
}

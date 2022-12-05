<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Hole;
use App\Model\Site;
use Hyperf\Di\Annotation\Inject;
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
                'site_id' => $site->id,
                'hole_code' => $row['hole_code']
            ], [
                'truck_id' => $truckId,
                'deep' => $row['deep'],
                'volume' => $row['volume'],
                'condition' => $row['condition']
            ]);
        }

        $total = $site->holes()->count();

        return $response->json([
            'success'   => true, 
            'message'   => 'Success sync data', 
            'total'     => $total
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Controller]
class SyncController
{

    #[RequestMapping(methods: "POST", path: "/sync/{truckId}")]
    public function receive(RequestInterface $request, ResponseInterface $response)
    {
        $meta = $request->input('meta', null);
        $data = $request->input('data', []);

        $total = count($data);

        return $response->json([
            'success' => true, 
            'message' => 'Success sync data', 
            'total' => $total
        ]);
    }
}

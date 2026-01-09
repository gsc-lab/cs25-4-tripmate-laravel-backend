<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * 성공 응답 (status 200)
     *
     * @param  mixed  $data  전달할 데이터
     * @param  string  $message  성공 메세지
     * @param  int  $status  기본 200
     */
    public function respondSuccess($data = null, string $message = '성공하였습니다.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * 성공 및 생성 (status 201)
     *
     * @param  mixed  $data
     */
    public function respondCreated($data = null, string $message = '생성되었습니다.'): JsonResponse
    {
        return $this->respondSuccess($data, $message, 201);
    }

    /**
     * 내용 없음 (status 204)
     * - 데이터 없이 성공인 경우
     */
    public function respondNoContent(): \Illuminate\Http\Response
    {
        return response()->noContent();
    }
}

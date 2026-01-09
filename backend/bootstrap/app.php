<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        /**
         * 공통 json 응답 포멧
         *
         * @param  string  $code
         * @param  string  $message
         * @param  int  $status
         * @param  array|null  $errors
         * @return \Illuminate\Http\JsonResponse
         */
        $jsonError = function (
            string $code,
            string $message,
            int $status,
            ?array $errors = null
        ) {
            $body = [
                'success' => false,
                'code' => $code,
                'message' => $message,
                'data' => null,
            ];

            if ($errors !== null) {
                $body['errors'] = $errors;
            }

            return response()->json($body, $status);
        };

        /**
         * API 요청인지 확인
         *
         * @param  \Illuminate\Http\Request  $request
         * @return bool
         */
        $isApiRequest = function (Request $request) {
            return $request->expectsJson();
        };

        /**
         * 인증 예외처리
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Illuminate\Validation\ValidationException  $e
         * @return \Illuminate\Http\JsonResponse|null
         */
        $exceptions->renderable(function (
            ValidationException $e,
            Request $request
        ) use (
            $jsonError,
            $isApiRequest
        ) {
            if (! $isApiRequest($request)) {
                return;
            }

            return $jsonError(
                'VALIDATION_ERROR',
                '요청 데이터가 유효하지 않습니다.',
                422,
                $e->errors()
            );
        });

        /**
         * 모델또는 라우트 리스소 없음 (404)
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Illuminate\Auth\AuthenticationException  $e
         * @return \Illuminate\Http\JsonResponse|null
         */
        $exceptions->renderable(function (
            ModelNotFoundException|NotFoundHttpException $e,
            Request $request
        ) use (
            $jsonError,
            $isApiRequest
        ) {
            if (! $isApiRequest($request)) {
                return;
            }

            return $jsonError(
                'RESOURCE_NOT_FOUND',
                '요청하신 리소스를 찾을 수 없습니다.',
                404
            );
        });

        /**
         * 인증 예외처리
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Illuminate\Auth\AuthenticationException  $e
         * @return \Illuminate\Http\JsonResponse|null
         */
        $exceptions->renderable(function (
            AuthenticationException $e,
            Request $request
        ) use (
            $jsonError,
            $isApiRequest
        ) {
            if (! $isApiRequest($request)) {
                return;
            }

            return $jsonError(
                'UNAUTHENTICATED',
                '인증이 필요합니다.',
                401
            );
        });

        /**
         * 인가 관련 예외처리
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Illuminate\Auth\Access\AuthorizationException  $e
         * @return \Illuminate\Http\JsonResponse|null
         */
        $exceptions->renderable(function (
            AuthorizationException $e,
            Request $request
        ) use (
            $jsonError,
            $isApiRequest
        ) {
            if (! $isApiRequest($request)) {
                return;
            }

            return $jsonError(
                'FORBIDDEN',
                '해당 리소스에 접근할 권한이 없습니다.',
                403
            );
        });

        /**
         * 일반 HTTP 예외처리
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Symfony\Component\HttpKernel\Exception\HttpException  $e
         * @return \Illuminate\Http\JsonResponse|null
         */
        $exceptions->renderable(function (
            HttpException $e,
            Request $request
        ) use (
            $jsonError,
            $isApiRequest
        ) {
            if (! $isApiRequest($request)) {
                return;
            }

            $status = $e->getStatusCode();

            // 상태 코드 별 code 매핑
            $code = match ($status) {
                400 => 'BAD_REQUEST',
                403 => 'FORBIDDEN',
                404 => 'RESOURCE_NOT_FOUND',
                default => 'HTTP_ERROR',
            };

            $message = $e->getMessage() ?: '요청을 처리할 수 없습니다.';

            return $jsonError(
                $code,
                $message,
                $status
            );
        });

        /**
         * 그 외 모든 예외처리
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Throwable  $e
         * @return \Illuminate\Http\JsonResponse|null
         */
        $exceptions->renderable(function (
            \Throwable $e,
            Request $request
        ) use (
            $isApiRequest
        ) {
            if (! $isApiRequest($request)) {
                return;
            }

            // 개발 모드
            if (config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'message' => $e->getMessage(),
                    'data' => null,
                    'exception' => get_class($e),
                    'trace' => $e->getTrace(),
                ], 500);
            }
        });

    })->create();

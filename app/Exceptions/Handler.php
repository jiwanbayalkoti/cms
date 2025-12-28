<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Check if request wants JSON (multiple ways to detect)
        $wantsJson = $request->expectsJson() 
            || $request->ajax() 
            || $request->wantsJson()
            || $request->header('Accept') === 'application/json'
            || $request->header('X-Requested-With') === 'XMLHttpRequest';
        
        // Handle validation exceptions for AJAX/JSON requests
        if ($e instanceof ValidationException) {
            if ($wantsJson) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $e->errors(),
                    'message' => 'The given data was invalid.'
                ], 422);
            }
        }
        
        // Handle 403/404/500 errors for AJAX requests
        if ($wantsJson && ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException || $e instanceof \Illuminate\Auth\AuthenticationException)) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            return response()->json([
                'error' => $e->getMessage() ?: 'An error occurred',
                'status' => $status
            ], $status);
        }

        return parent::render($request, $e);
    }
}

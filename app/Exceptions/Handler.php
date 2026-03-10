<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Session\TokenMismatchException;
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

        // Handle CSRF Token Mismatch - redirect back with error instead of 419 page
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session Expired',
                    'error' => 'Your session has expired. Please refresh the page and try again.',
                    'error_code' => 'CSRF_TOKEN_MISMATCH'
                ], 419);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Your session has expired. Please try again.');
        });

        // Handle Database/SQL Exceptions
        $this->renderable(function (QueryException $e, $request) {
            return $this->handleDatabaseException($e, $request);
        });

        // Handle Model Not Found
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return $this->handleModelNotFoundException($e, $request);
        });
    }

    /**
     * Handle database/SQL exceptions with user-friendly messages
     *
     * @param QueryException $e
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleDatabaseException(QueryException $e, $request)
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        // Parse the SQL error and provide user-friendly message
        $userMessage = $this->parseDatabaseError($errorCode, $errorMessage);
        
        // Log the actual error for debugging
        \Log::error('Database Error: ' . $errorMessage, [
            'code' => $errorCode,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'sql' => $e->getSql() ?? 'N/A',
        ]);

        // Return JSON response for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $userMessage['title'],
                'error' => $userMessage['description'],
                'error_code' => 'DATABASE_ERROR'
            ], 500);
        }

        // Return redirect with error for web requests
        return redirect()->back()
            ->withInput()
            ->with('error', $userMessage['title'] . ' ' . $userMessage['description']);
    }

    /**
     * Handle Model Not Found exceptions
     *
     * @param ModelNotFoundException $e
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleModelNotFoundException(ModelNotFoundException $e, $request)
    {
        $model = class_basename($e->getModel());
        $userMessage = "The requested {$model} could not be found.";

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Resource Not Found',
                'error' => $userMessage,
                'error_code' => 'NOT_FOUND'
            ], 404);
        }

        return redirect()->back()
            ->with('error', $userMessage);
    }

    /**
     * Parse database error codes and return user-friendly messages
     *
     * @param string|int $errorCode
     * @param string $errorMessage
     * @return array
     */
    protected function parseDatabaseError($errorCode, string $errorMessage): array
    {
        // MySQL/MariaDB error codes
        $errorMappings = [
            // Connection errors
            '2002' => [
                'title' => 'Database Connection Failed',
                'description' => 'Unable to connect to the database server. Please try again later or contact support.'
            ],
            '1045' => [
                'title' => 'Database Access Denied',
                'description' => 'Database authentication failed. Please contact the administrator.'
            ],
            '1049' => [
                'title' => 'Database Not Found',
                'description' => 'The specified database does not exist. Please contact the administrator.'
            ],
            
            // Table/Column errors
            '1146' => [
                'title' => 'Database Table Missing',
                'description' => 'A required database table is missing. Please run database migrations or contact support.'
            ],
            '1054' => [
                'title' => 'Database Column Missing',
                'description' => 'A required database column is missing. Please run database migrations or contact support.'
            ],
            
            // Constraint violations
            '1062' => [
                'title' => 'Duplicate Entry',
                'description' => 'This record already exists. Please use a unique value.'
            ],
            '1451' => [
                'title' => 'Cannot Delete Record',
                'description' => 'This record cannot be deleted because it is being used by other records.'
            ],
            '1452' => [
                'title' => 'Invalid Reference',
                'description' => 'The referenced record does not exist. Please select a valid option.'
            ],
            '23000' => [
                'title' => 'Data Integrity Error',
                'description' => 'The operation violates data integrity constraints. Please check your input.'
            ],
            
            // Data errors
            '1264' => [
                'title' => 'Value Out of Range',
                'description' => 'The provided value is out of the allowed range. Please enter a valid value.'
            ],
            '1406' => [
                'title' => 'Data Too Long',
                'description' => 'The provided text is too long. Please shorten your input.'
            ],
            '1366' => [
                'title' => 'Invalid Data Format',
                'description' => 'The provided data format is invalid. Please check your input.'
            ],
            
            // Syntax errors
            '1064' => [
                'title' => 'Database Query Error',
                'description' => 'There was an error processing your request. Please try again or contact support.'
            ],
            
            // Lock/Timeout errors
            '1205' => [
                'title' => 'Request Timeout',
                'description' => 'The database operation timed out. Please try again.'
            ],
            '1213' => [
                'title' => 'Temporary Conflict',
                'description' => 'A temporary conflict occurred. Please try again.'
            ],
            
            // SQLite errors
            'HY000' => [
                'title' => 'Database Error',
                'description' => 'A database error occurred. Please try again or contact support.'
            ],
            '19' => [
                'title' => 'Constraint Violation',
                'description' => 'The operation violates a database constraint. Please check your input.'
            ],
        ];

        // Check for specific error code
        if (isset($errorMappings[$errorCode])) {
            return $errorMappings[$errorCode];
        }

        // Check for SQLSTATE codes (format: SQLSTATE[XXXXX])
        if (preg_match('/SQLSTATE\[(\w+)\]/', $errorMessage, $matches)) {
            $sqlState = $matches[1];
            if (isset($errorMappings[$sqlState])) {
                return $errorMappings[$sqlState];
            }
        }

        // Check for specific error patterns in message
        if (stripos($errorMessage, 'duplicate') !== false || stripos($errorMessage, 'unique constraint') !== false) {
            return [
                'title' => 'Duplicate Entry',
                'description' => 'This record already exists. Please use a unique value.'
            ];
        }

        if (stripos($errorMessage, 'foreign key constraint') !== false) {
            if (stripos($errorMessage, 'delete') !== false || stripos($errorMessage, 'update') !== false) {
                return [
                    'title' => 'Cannot Modify Record',
                    'description' => 'This record is linked to other data and cannot be modified.'
                ];
            }
            return [
                'title' => 'Invalid Reference',
                'description' => 'The referenced record does not exist.'
            ];
        }

        if (stripos($errorMessage, 'connection') !== false || stripos($errorMessage, 'connect') !== false) {
            return [
                'title' => 'Connection Error',
                'description' => 'Unable to connect to the database. Please try again later.'
            ];
        }

        if (stripos($errorMessage, 'table') !== false && stripos($errorMessage, 'exist') !== false) {
            return [
                'title' => 'Database Setup Required',
                'description' => 'The database is not properly configured. Please contact the administrator.'
            ];
        }

        // Default error message
        return [
            'title' => 'Database Error',
            'description' => 'An unexpected database error occurred. Please try again or contact support if the problem persists.'
        ];
    }
}

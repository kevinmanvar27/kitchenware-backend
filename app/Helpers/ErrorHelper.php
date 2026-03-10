<?php

/**
 * Error Helper Functions
 * 
 * Provides user-friendly error message handling for the application
 */

if (!function_exists('formatDatabaseError')) {
    /**
     * Format a database error into a user-friendly message
     *
     * @param \Exception $exception
     * @return array
     */
    function formatDatabaseError($exception): array
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();
        
        // Common error mappings
        $errorMappings = [
            '2002' => [
                'title' => 'Connection Error',
                'message' => 'Unable to connect to the database. Please try again later.',
                'type' => 'danger'
            ],
            '1045' => [
                'title' => 'Access Denied',
                'message' => 'Database access denied. Please contact the administrator.',
                'type' => 'danger'
            ],
            '1062' => [
                'title' => 'Duplicate Entry',
                'message' => 'This record already exists. Please use a unique value.',
                'type' => 'warning'
            ],
            '1451' => [
                'title' => 'Cannot Delete',
                'message' => 'This record cannot be deleted because it is being used elsewhere.',
                'type' => 'warning'
            ],
            '1452' => [
                'title' => 'Invalid Reference',
                'message' => 'The referenced record does not exist.',
                'type' => 'warning'
            ],
            '23000' => [
                'title' => 'Data Error',
                'message' => 'The operation could not be completed due to data constraints.',
                'type' => 'warning'
            ],
        ];
        
        // Check for specific error code
        if (isset($errorMappings[$errorCode])) {
            return $errorMappings[$errorCode];
        }
        
        // Check for SQLSTATE codes
        if (preg_match('/SQLSTATE\[(\w+)\]/', $errorMessage, $matches)) {
            $sqlState = $matches[1];
            if (isset($errorMappings[$sqlState])) {
                return $errorMappings[$sqlState];
            }
        }
        
        // Check for specific patterns
        if (stripos($errorMessage, 'duplicate') !== false) {
            return [
                'title' => 'Duplicate Entry',
                'message' => 'This record already exists.',
                'type' => 'warning'
            ];
        }
        
        if (stripos($errorMessage, 'foreign key') !== false) {
            return [
                'title' => 'Reference Error',
                'message' => 'This operation cannot be completed due to related records.',
                'type' => 'warning'
            ];
        }
        
        if (stripos($errorMessage, 'connection') !== false) {
            return [
                'title' => 'Connection Error',
                'message' => 'Unable to connect to the database. Please try again.',
                'type' => 'danger'
            ];
        }
        
        // Default error
        return [
            'title' => 'Error',
            'message' => 'An unexpected error occurred. Please try again or contact support.',
            'type' => 'danger'
        ];
    }
}

if (!function_exists('getUserFriendlyErrorMessage')) {
    /**
     * Get a user-friendly error message from an exception
     *
     * @param \Exception $exception
     * @return string
     */
    function getUserFriendlyErrorMessage($exception): string
    {
        $error = formatDatabaseError($exception);
        return $error['title'] . ': ' . $error['message'];
    }
}

if (!function_exists('isValidationError')) {
    /**
     * Check if an exception is a validation error
     *
     * @param \Exception $exception
     * @return bool
     */
    function isValidationError($exception): bool
    {
        return $exception instanceof \Illuminate\Validation\ValidationException;
    }
}

if (!function_exists('isDatabaseError')) {
    /**
     * Check if an exception is a database error
     *
     * @param \Exception $exception
     * @return bool
     */
    function isDatabaseError($exception): bool
    {
        return $exception instanceof \Illuminate\Database\QueryException;
    }
}

if (!function_exists('logError')) {
    /**
     * Log an error with context
     *
     * @param \Exception $exception
     * @param array $context
     * @return void
     */
    function logError($exception, array $context = []): void
    {
        \Log::error($exception->getMessage(), array_merge([
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ], $context));
    }
}

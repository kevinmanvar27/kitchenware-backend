<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    /**
     * Log an admin activity.
     *
     * @param string $action
     * @param string $description
     * @param mixed $model
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return ActivityLog
     */
    protected function logAdminActivity($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        return ActivityLog::logAdmin($action, $description, $model, $oldValues, $newValues);
    }

    /**
     * Log a vendor activity.
     *
     * @param int $vendorId
     * @param string $action
     * @param string $description
     * @param mixed $model
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return ActivityLog
     */
    protected function logVendorActivity($vendorId, $action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        return ActivityLog::logVendor($vendorId, $action, $description, $model, $oldValues, $newValues);
    }

    /**
     * Get the current vendor ID for logging.
     *
     * @return int|null
     */
    protected function getCurrentVendorId()
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        // If user is a vendor owner
        if ($user->isVendor() && $user->vendor) {
            return $user->vendor->id;
        }

        // If user is vendor staff
        if ($user->isVendorStaff() && $user->vendorStaff) {
            return $user->vendorStaff->vendor_id;
        }

        return null;
    }

    /**
     * Get changes between old and new model values.
     *
     * @param mixed $model
     * @param array $oldValues
     * @return array
     */
    protected function getModelChanges($model, $oldValues)
    {
        $newValues = $model->getAttributes();
        $changes = [];

        // Fields to exclude from logging
        $excludedFields = ['password', 'remember_token', 'updated_at', 'created_at'];

        foreach ($newValues as $key => $value) {
            if (in_array($key, $excludedFields)) {
                continue;
            }

            if (!isset($oldValues[$key]) || $oldValues[$key] !== $value) {
                $changes[$key] = [
                    'old' => $oldValues[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }
}

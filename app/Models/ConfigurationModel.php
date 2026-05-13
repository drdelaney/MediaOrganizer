<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigurationModel extends Model
{
    protected $table = 'configuration';
    protected $primaryKey = 'param';
    protected $returnType = 'array';
    protected $allowedFields = ['param', 'value'];

    /**
     * Get a configuration value by parameter name.
     * Environment variables always override database values.
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function getParam(string $param, $default = null)
    {
        // Check environment first (allow entries in .env to override database)
        // CodeIgniter 4 uses dot notation for env vars, e.g., email.protocol
        $envValue = env($param);
        if ($envValue !== null) {
            return $envValue;
        }

        // Handle migration from musicbrainz_user_agent to user_agent
        if ($param === 'user_agent') {
            $row = $this->where('param', 'user_agent')->first();
            if (!$row) {
                // Check if old parameter exists
                $oldRow = $this->where('param', 'musicbrainz_user_agent')->first();
                if ($oldRow) {
                    // Migrate it
                    $this->setParam('user_agent', $oldRow['value']);
                    $this->delete('musicbrainz_user_agent');
                    return $oldRow['value'];
                }
            }
        }

        $row = $this->where('param', $param)->first();
        return $row ? $row['value'] : $default;
    }

    /**
     * Ensure a configuration parameter exists in the database
     *
     * @param string $param
     * @param string $default
     * @return void
     */
    public function ensureParam(string $param, string $default): void
    {
        if (!$this->where('param', $param)->first()) {
            $this->setParam($param, $default);
        }
    }

    /**
     * Set a configuration value
     *
     * @param string $param
     * @param string $value
     * @return bool
     */
    public function setParam(string $param, string $value): bool
    {
        try {
            // Ensure no duplicate entries for the same param
            $existing = $this->where('param', $param)->first();
            if ($existing) {
                // Use builder directly to avoid any model-level truncation or validation issues
                return $this->builder()->where('param', $param)->update(['value' => $value]);
            }

            return $this->builder()->insert([
                'param' => $param,
                'value' => $value
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ConfigurationModel::setParam failed for {param}: {error}', [
                'param' => $param,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

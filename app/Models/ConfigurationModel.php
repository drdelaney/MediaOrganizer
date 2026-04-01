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
     * Get a configuration value by parameter name
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function getParam(string $param, $default = null)
    {
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
        if ($this->where('param', $param)->first()) {
            return $this->update($param, ['value' => $value]);
        }

        return (bool)$this->insert([
            'param' => $param,
            'value' => $value
        ]);
    }
}

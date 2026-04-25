<?php

declare(strict_types = 1);

namespace Centrex\Crm\Models;

use Centrex\Crm\Concerns\AddTablePrefix;
use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    use AddTablePrefix;

    protected function getTableSuffix(): string
    {
        return 'email_settings';
    }

    protected $fillable = [
        'key',
        'value',
    ];

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('crm.drivers.database.connection', config('database.default')));
    }
}

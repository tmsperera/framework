<?php

namespace Illuminate\Tests\Integration\Database\EloquentModelRelationPathLoadedTest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Tests\Integration\Database\DatabaseTestCase;

class EloquentModelRelationPathLoadedTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('ones', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('twos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('one_id');
        });

        Schema::create('threes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('two_id');
        });
    }

    public function testWhenRelationNotLoaded()
    {
        $one = One::query()->create();
        $one->twos()->create();

        $model = One::query()->find($one->id);

        $this->assertFalse($model->relationPathLoaded('twos'));
    }

    public function testWhenRelationLoaded()
    {
        $one = One::query()->create();
        $one->twos()->create();

        $model = One::query()
            ->with('twos')
            ->find($one->id);

        $this->assertTrue($model->relationPathLoaded('twos'));
    }

    public function testWhenChildRelationIsNotLoaded()
    {
        $one = One::query()->create();
        $two = $one->twos()->create();
        $two->threes()->create();

        $model = One::query()
            ->with('twos')
            ->find($one->id);

        $this->assertTrue($model->relationPathLoaded('twos'));
        $this->assertFalse($model->relationPathLoaded('twos.threes'));
    }

    public function testWhenChildRelationIsLoaded()
    {
        $one = One::query()->create();
        $two = $one->twos()->create();
        $two->threes()->create();

        $model = One::query()
            ->with('twos.threes')
            ->find($one->id);

        $this->assertTrue($model->relationPathLoaded('twos'));
        $this->assertTrue($model->relationPathLoaded('twos.threes'));
    }
}

class One extends Model
{
    public $table = 'ones';
    public $timestamps = false;
    protected $guarded = [];

    public function twos(): HasMany
    {
        return $this->hasMany(Two::class, 'one_id');
    }
}

class Two extends Model
{
    public $table = 'twos';
    public $timestamps = false;
    protected $guarded = [];

    public function one(): BelongsTo
    {
        return $this->belongsTo(One::class, 'one_id');
    }

    public function threes(): HasMany
    {
        return $this->hasMany(Three::class, 'two_id');
    }
}

class Three extends Model
{
    public $table = 'threes';
    public $timestamps = false;
    protected $guarded = [];

    public function two(): BelongsTo
    {
        return $this->belongsTo(Two::class, 'two_id');
    }
}

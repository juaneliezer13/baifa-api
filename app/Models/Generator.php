<?php

namespace App\Models;

use App\Enums\GeneratorStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Generator extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'serial_number',
        'client_id',
        'name',
        'model',
        'capacity_kva',
        'status',
        'estimated_arrival_date',
        'photo_path',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GeneratorStatus::class,
            'capacity_kva' => 'float',
            'estimated_arrival_date' => 'date:Y-m-d',
        ];
    }

    /**
     * URL completa de acceso a la fotografía referencial.
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->photo_path) {
                    return null;
                }

                if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
                    return $this->photo_path;
                }

                return Storage::disk('public')->url($this->photo_path);
            }
        );
    }

    /**
     * Cliente asignado al generador (ficha fiscal).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Historial cronológico de puntos de control (checkpoints) y trazabilidad.
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(Checkpoint::class)->orderBy('event_date', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Último punto de control registrado para el generador.
     */
    public function latestCheckpoint(): HasOne
    {
        return $this->hasOne(Checkpoint::class)->latestOfMany('event_date');
    }

    /**
     * Scope para filtrar por estado específico.
     */
    public function scopeByStatus(Builder $query, string|GeneratorStatus $status): Builder
    {
        $statusValue = $status instanceof GeneratorStatus ? $status->value : $status;

        return $query->where('status', $statusValue);
    }

    /**
     * Scope para filtrar por cliente.
     */
    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope para realizar búsquedas textuales por serial, modelo, nombre o cliente.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('serial_number', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhereHas('client', function (Builder $clientQuery) use ($term) {
                    $clientQuery->where('company_fiscal_name', 'like', "%{$term}%")
                        ->orWhere('company_short_name', 'like', "%{$term}%")
                        ->orWhere('rif', 'like', "%{$term}%");
                });
        });
    }
}

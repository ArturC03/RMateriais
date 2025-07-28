<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property mixed $status
 * @property \Illuminate\Support\Carbon $requested_at
 * @property \Illuminate\Support\Carbon $returned_at
 * @property \Illuminate\Support\Carbon $approved_at
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequestItem> $requestItems
 * @property-read int|null $request_items_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\RequestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereReturnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Request whereUserId($value)
 * @mixin \Eloquent
 */
class Request extends Model
{

    use HasFactory;
    protected $fillable = [
        'user_id',
        'status',        // e.g., 'rascunho', 'pendente', 'reservado', 'devolvido', 'cancelado'
        'requested_at',  // when the request was made
        'approved_at',   // when the professor approved it
        'returned_at',   // when the student returned the materials
    ];

    protected $dates = [
        'requested_at',
        'approved_at',
        'returned_at'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function requestItems(): HasMany {
        return $this->hasMany(RequestItem::class);
    }

    public function isPending(): bool {
        return $this->status === 'pendente';
    }

    public function isReserved(): bool {
        return $this->status === 'reservado';
    }

    public function isReturned(): bool {
        return $this->status === 'devolvido';
    }

    public function isCancelled(): bool {
        return $this->status === 'cancelado';
    }

    public function isDraft(): bool {
        return $this->status === 'rascunho';
    }

    public static function createDraftForUser(User $user): self {
        return self::create([
            'user_id' => $user->id,
            'status' => 'rascunho',
        ]);
    }

    public function isEmpty(): bool {
        return $this->requestItems()->count() === 0;
    }

    public function refreshDueDates(): void {
        foreach ($this->requestItems() as $requestItem) {
            $requestItem->refreshDueDate();
            $requestItem->save();
        }
    }

    public function refreshReturnDates(): void {
        foreach ($this->requestItems() as $requestItem) {
            $requestItem->refreshReturnDate();
            $requestItem->save();
        }
    }

    /**
     * @throws \Exception
     */
    public function makeOrder(): void
    {
        try {
            if ($this->status !== 'rascunho')
                throw new \Exception('Tentativa de criar pedido de uma requisição que não é um rascunho');

            $this->refreshDueDates();
            $this->requested_at = now();
            $this->status = 'pendente';
            $this->save();
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        // Send email notification to professors
        $this->sendOrderNotification();
    }

    /**
     * Professor confirms the request and reserves the materials
     * This happens after the professor gives the materials to the student
     */
    public function confirmAndReserve(): void
    {
        if ($this->status !== 'pendente') {
            throw new \Exception('Apenas requisições pendentes podem ser confirmadas');
        }

        $this->status = 'reservado';
        $this->approved_at = now();
        $this->save();

        // Mark all items as reserved
        foreach ($this->requestItems as $item) {
            $item->reserved_at = now();
            $item->refreshDueDate(); // Set due date to 3 days from now
            $item->save();
        }
    }

    /**
     * Mark the request as returned
     */
    public function markAsReturned(): void
    {
        if ($this->status !== 'reservado') {
            throw new \Exception('Apenas requisições reservadas podem ser marcadas como devolvidas');
        }

        $this->status = 'devolvido';
        $this->returned_at = now();
        $this->save();

        // Mark all items as returned
        foreach ($this->requestItems as $item) {
            $item->returned = true;
            $item->save();
        }
    }

    /**
     * Cancel the request
     */
    public function cancel(): void
    {
        if (!in_array($this->status, ['rascunho', 'pendente'])) {
            throw new \Exception('Apenas rascunhos ou requisições pendentes podem ser canceladas');
        }

        $this->status = 'cancelado';
        $this->save();
    }

    /**
     * Send email notification to professors about the new order
     */
    private function sendOrderNotification(): void
    {
        // Get all professors
        $professors = \App\Models\User::where('role', 'professor')->get();

        if($professors->isEmpty()) {
            // Send to fallback email if no professors exist
            \Illuminate\Support\Facades\Mail::to("arturvicentecruz@proton.me")
                ->send(new \App\Mail\OrderNotification($this));
            return;
        }

        // Send email to each professor
        foreach ($professors as $professor) {
            \Illuminate\Support\Facades\Mail::to($professor->email)
                ->send(new \App\Mail\OrderNotification($this));
        }
    }
}

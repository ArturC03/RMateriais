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
 */
class Request extends Model
{

    use HasFactory;
    protected $fillable = [
        'user_id',
        'status',        // e.g., 'pendente', 'aprovado', 'rejeitado', 'devolvido', 'rascunho'
        'requested_at',  // when the request was made 'approved_at',   // when the professor approved it
        'approved_at',
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

    public function isApproved(): bool {
        return $this->status === 'aprovado';
    }

    public function isReturned(): bool {
        return $this->status === 'devolvido';
    }

    public function isRejected(): bool {
        return $this->status === 'recusado';
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

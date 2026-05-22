<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    protected $fillable = ['client_id', 'uploaded_by', 'name', 'file_path', 'file_type', 'file_size', 'category'];
    public function client() { return $this->belongsTo(Client::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}

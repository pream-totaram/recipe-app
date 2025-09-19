<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'instructions',
        'prep_time',
        'cook_time',
        'servings',
        'difficulty',
        'cuisine_type',
        'image_path',
        'is_public'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute() {
        if($this->image_path) {
            return Storage::url($this->image_path);
        }
    }

    public function addImage($imagePath) {
        $this->update(['image_path' => $imagePath]);
    }

     // Method to delete old image when updating
    public function deleteImage()
    {
        if ($this->image_path && Storage::exists($this->image_path)) {
            Storage::delete($this->image_path);
        }
    }
}

<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Analysis représente le résultat du matching IA entre un CV et
 * l'offre d'emploi à laquelle il a été soumis.
 *
 * Relation : Analysis appartient à 1 CV (belongsTo), relation
 * inverse de Cv::analysis().
 *
 * Rappel métier CRITIQUE : une fois status = COMPLETED, ce record
 * est IMMUABLE. Cette règle n'est pas encore appliquée ici au niveau
 * du Model (on le fera via un Observer ou une policy plus tard,
 * à l'étape des Controllers/Services) — pour l'instant, le Model
 * ne fait que décrire la structure et les relations.
 */
class Analysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'status',
        'similarity_score',
        'justification',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            /**
             * 'status' => AnalysisStatus::class
             *
             * C'est le cast ENUM natif de Laravel (disponible depuis
             * Laravel 9+, adapté aux "backed enums" PHP 8.1+).
             *
             * Sans ce cast : $analysis->status te renverrait la
             * STRING brute 'pending' stockée en base.
             *
             * Avec ce cast : $analysis->status te renvoie directement
             * l'instance de l'enum AnalysisStatus::PENDING. Tu peux
             * alors écrire du code sûr et lisible comme :
             *   if ($analysis->status === AnalysisStatus::COMPLETED)
             * au lieu de comparer des strings brutes sujettes aux
             * fautes de frappe (if ($analysis->status === 'compelted')
             * ne serait jamais détecté par PHP sans ce cast).
             */
            'status' => AnalysisStatus::class,

            // similarity_score est stocké en decimal(5,4) en base ;
            // ce cast garantit qu'il est toujours manipulé comme un
            // float en PHP (et non comme une string, ce que MySQL/PDO
            // renvoie parfois par défaut pour les colonnes DECIMAL).
            'similarity_score' => 'float',

            // analyzed_at est un TIMESTAMP SQL ; ce cast le convertit
            // automatiquement en instance Carbon (date/heure
            // manipulable facilement : ->format(), ->diffForHumans(),
            // etc.) au lieu d'une string brute.
            'analyzed_at' => 'datetime',
        ];
    }

    /**
     * Relation inverse de Cv::analysis().
     * Une analyse appartient à exactement un CV.
     *
     * Utilisation : $analysis->cv récupère l'objet Cv lié.
     */
    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}
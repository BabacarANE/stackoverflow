<?php
// app/Http/Controllers/QuestionController.php

namespace App\Http\Controllers;

use App\Models\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * Afficher toutes les questions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questions = Question::with('user', 'answers')->latest()->get();
        return response()->json($questions, 200);
    }

    /**
     * Afficher une question spécifique avec ses réponses.
     *
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function show(Question $question)
    {
        $question->load('user', 'answers');
        return response()->json($question, 200);
    }

    /**
     * Créer une nouvelle question.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Création de la question
        $question = Question::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        // Association des tags à la question
        if ($request->has('tags')) {
            $question->tags()->attach($request->tags);
        }

        return response()->json(['message' => 'Question créée avec succès', 'question' => $question], 201);
    }

    /**
     * Mettre à jour une question existante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Question $question)
    {
        // Vérification de l'autorisation (seul l'utilisateur qui a posé la question peut la modifier)
        if ($request->user()->cannot('update', $question)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à effectuer cette action'], 403);
        }

        // Validation des données
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Mise à jour de la question
        $question->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Question mise à jour avec succès', 'question' => $question], 200);
    }

    /**
     * Supprimer une question existante.
     *
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {
        // Vérification de l'autorisation (seul l'utilisateur qui a posé la question peut la supprimer)
        if (Auth::id() !== $question->user_id) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à effectuer cette action'], 403);
        }

        // Suppression de la question
        $question->delete();

        return response()->json(['message' => 'Question supprimée avec succès'], 200);
    }
}

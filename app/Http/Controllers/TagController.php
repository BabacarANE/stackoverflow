<?php
// app/Http/Controllers/TagController.php

namespace App\Http\Controllers;

use App\Models\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Afficher tous les tags.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = Tag::all();
        return response()->json($tags, 200);
    }

    /**
     * Afficher un tag spécifique.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        return response()->json($tag, 200);
    }

    /**
     * Créer un nouveau tag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|unique:tags|max:255',
        ]);

        // Création du tag
        $tag = Tag::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Tag créé avec succès', 'tag' => $tag], 201);
    }

    /**
     * Mettre à jour un tag existant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tag $tag)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);

        // Mise à jour du tag
        $tag->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Tag mis à jour avec succès', 'tag' => $tag], 200);
    }

    /**
     * Supprimer un tag existant.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        // Suppression du tag
        $tag->delete();

        return response()->json(['message' => 'Tag supprimé avec succès'], 200);
    }
}

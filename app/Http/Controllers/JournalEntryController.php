<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;

class JournalEntryController extends Controller
{
    public function index()
    {
        $entries = JournalEntry::withCount('lines')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('journal.index', compact('entries'));
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load([
            'lines.account',
            'lines.customer',
            'lines.supplier',
            'lines.product',
            'lines.invoice',
        ]);

        return view('journal.show', compact('journalEntry'));
    }
}
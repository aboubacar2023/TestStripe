<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Gestion des cautions</title>
</head>

<body>

    <div class="max-w-2xl mx-auto bg-white shadow p-6 rounded-lg">
        <h2 class="text-xl font-bold mb-4">Gestion des cautions</h2>

        @foreach ($cautions as $caution)
            <div class="border-b py-4">
                <p><strong>Utilisateur:</strong> {{ $caution->user->name }}</p>
                <p><strong>Montant bloqué :</strong> {{ $caution->montant }} €</p>
                @if ($caution->status === 'capture')
                    <p><strong>Montant capturé :</strong> {{ $caution->montant_paye }} €</p>
                @endif
                <p><strong>Status :</strong> {{ $caution->status }}</p>

                @if ($caution->status === 'bloque')
                    <form action="{{ route('cautions.capture', $caution) }}" method="POST"
                        class="mt-2 flex items-center gap-2">
                        @csrf
                        <input type="text" name="amount" max="{{ $caution->amount }}"
                            placeholder="Montant à capturer (€)" class="border rounded px-3 py-1" required>
                        <button type="submit" class="bg-green-500 text-white px-4 py-1 rounded">Capturer</button>
                    </form>

                    <form action="{{ route('cautions.annule', $caution) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white px-4 py-1 rounded">Libérer</button>
                    </form>
                @endif
            </div>
        @endforeach
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-2">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-2">
                {{ session('error') }}
            </div>
        @endif

    </div>
</body>

</html>

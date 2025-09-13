<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Suivi essaie</title>
</head>
<body>
    
<div class="max-w-md mx-auto p-6 bg-white rounded shadow">
  @if($caution && $caution->status === 'pending')
    <h2 class="text-lg font-bold">Essai en cours</h2>
    <p class="mt-2">Fin de l'essai : {{ $caution->end_date->format('d/m/Y') }}</p>
    <p class="mt-2">Montant autorisé : {{ number_format($caution->montant, 2, ',', ' ') }} €</p>

    <form method="POST" action="{{ route('essaie.confirm', $caution) }}" class="mt-4">
      @csrf
      <button class="w-full bg-green-600 text-white py-2 rounded">Confirmer l'abonnement (capturer)</button>
    </form>

    <form method="POST" action="{{ route('essaie.cancel', $caution) }}" class="mt-2">
      @csrf
      <button class="w-full bg-red-500 text-white py-2 rounded">Annuler l'essai (libérer la caution)</button>
    </form>
  @else
    <p class="text-center text-gray-600">Vous n'avez pas d'essai actif.</p>
  @endif
</div>
</body>
</html>

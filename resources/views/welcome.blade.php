<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélection produit - Paiement Stripe</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto p-8 mt-10 bg-white rounded-2xl shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Sélectionner un produit</h2>

        <form action="/feature-1" method="POST" class="space-y-6">
            {{-- recuperation des produits depuis la DB --}}
            @csrf
            @php
                $produits = App\Models\Produit::all();
            @endphp
            @foreach ($produits as $item)
                <label class="flex items-center justify-between p-4 border rounded-xl cursor-pointer hover:shadow-md">
                    <div>
                        <div class="font-medium text-gray-900">{{$item->nom_produit}}</div>
                        <div class="text-sm text-gray-600">{{$item->price}} €</div>
                    </div>
                    <input type="radio" name="product_id" value="{{$item->id}}" class="h-5 w-5 text-indigo-600" required>
                </label>
            @endforeach

            <!-- Quantité -->
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2">
                    <span class="text-sm text-gray-700">Quantité</span>
                    <input type="number" name="quantite" min="1" placeholder="1"
                        class="w-20 p-2 border rounded-md" required>
                </label>
            </div>
            <div class="pt-4">
                <button type="submit"
                    class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition">
                    Payer avec Stripe
                </button>
            </div>
        </form>
    </div>
</body>

</html>

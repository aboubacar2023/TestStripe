<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Location</title>
</head>

<body>
    <div class="mt-10 max-w-2xl mx-auto p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">Louer une voiture à 400 € avec une caution 1000€</h1>
        <div class="mt-4">
            <button class="px-4 py-2 bg-indigo-600 text-white rounded"><a href="{{ route('validation') }}">valider</a></button>
        </div>
    </div>
</body>

</html>

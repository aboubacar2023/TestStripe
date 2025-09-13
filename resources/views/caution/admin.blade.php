<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>

    <title>Admin</title>
</head>
<body>
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Cautions</h1>
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Montant</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cautions as $c)
                    <tr class="border-t">
                        <td>{{ $c->id }}</td>
                        <td>{{ $c->user->email }}</td>
                        <td>{{ number_format($c->montant, 2, ',', ' ') }} €</td>
                        <td>{{ $c->status }}</td>
                        <td>
                            <form action="{{ route('admin.cautions.capture', $c) }}" method="POST"
                                style="display:inline">
                                @csrf
                                <button class="px-2 py-1 bg-green-500 text-white rounded">Capturer</button>
                            </form>
                            <form action="{{ route('admin.cautions.cancel', $c) }}" method="POST"
                                style="display:inline">
                                @csrf
                                <button class="px-2 py-1 bg-red-500 text-white rounded">Annuler</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $cautions->links() }}</div>
    </div>
</body>

</html>

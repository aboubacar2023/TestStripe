<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Paiement Stripe</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        #card-element {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 12px;
            height: 20px;
        }

        #card-errors {
            color: red;
            margin-top: 8px;
        }
    </style>
</head>

<body>

    @php
    @endphp
    <h2>Paiement de {{$price}} € pour le produit : {{$produit}}</h2>

    <form id="payment-form">
        <div id="card-element"></div>
        <div id="card-errors"></div>
        <button class="bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition" type="submit" id="submit">Payer</button>
    </form>

    <script>
        const stripe = Stripe("{{ $stripeKey }}");
        const elements = stripe.elements();

        // Création du champ carte
        const card = elements.create("card", {
            hidePostalCode: true,
            style: {
                base: {
                    fontSize: "16px",
                    color: "#32325d",
                    '::placeholder': {
                        color: "#aab7c4"
                    },
                },
                invalid: {
                    color: "#fa755a"
                }
            }
        });
        card.mount("#card-element");

        // Gestion des erreurs en temps réel
        card.on("change", (event) => {
            document.getElementById("card-errors").textContent = event.error ? event.error.message : "";
        });

        // Soumission du paiement
        const form = document.getElementById("payment-form");

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const {
                error,
                paymentIntent
            } = await stripe.confirmCardPayment(
                "{{ $clientSecret }}", {
                    payment_method: {
                        card: card
                    }
                }
            );

            if (error) {
                document.getElementById("card-errors").textContent = error.message;
                window.location.href = "/cancel"; // redirection si erreur
            } else if (paymentIntent.status === "succeeded") {
                window.location.href = "/success"; // redirection si succès
            }
        });
    </script>
</body>

</html>

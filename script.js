// Animation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Animation des cartes produits
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'opacity 0.5s, transform 0.5s';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Confirmation avant suppression d'un article du panier
    const removeForms = document.querySelectorAll('.item-remove form');
    removeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Voulez-vous vraiment supprimer cet article ?')) {
                e.preventDefault();
            }
        });
    });

    // Animation du bouton d'ajout au panier
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const button = this.querySelector('.btn-add-cart');
            button.textContent = '✓ Ajouté !';
            button.style.background = '#48bb78';

            setTimeout(() => {
                button.textContent = 'Ajouter au panier';
                button.style.background = '';
            }, 1500);
        });
    });

    // Messages de succès qui disparaissent
    const successMessages = document.querySelectorAll('.success-message');
    successMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 3000);
    });
});

// Fonction pour mettre à jour le compteur du panier
function updateCartCount() {
    const cartItems = document.querySelectorAll('.cart-item');
    const cartCount = cartItems.length;
    const cartBadges = document.querySelectorAll('.nav-menu a[href="cart.php"]');

    cartBadges.forEach(badge => {
        const text = badge.textContent.replace(/\(\d+\)/, `(${cartCount})`);
        badge.textContent = text;
    });
}

// Smooth scroll pour les liens internes
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
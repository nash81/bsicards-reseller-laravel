// This file is deprecated and should be removed. Use the new Digital Visa Wallet Card JS logic in the main views.

document.addEventListener('DOMContentLoaded', function() {
    fetchCards();
    document.getElementById('createCardBtn').addEventListener('click', function() {
        $('#createCardModal').modal('show');
    });
    document.getElementById('createCardForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createCard();
    });
});

function fetchCards() {
    fetch('/user/reseller-digital-visa-cards')
        .then(response => response.text())
        .then(html => {
            document.getElementById('visaWalletCardsList').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('visaWalletCardsList').innerHTML = '<div class="alert alert-danger">Failed to load cards.</div>';
        });
}

function createCard() {
    const form = document.getElementById('createCardForm');
    const formData = new FormData(form);
    fetch('/user/reseller-digital-visa-create', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        $('#createCardModal').modal('hide');
        fetchCards();
    })
    .catch(() => {
        alert('Failed to create card.');
    });
}
// Add similar JS functions for fund, block, unblock, get OTP as needed


function addToCart(btn) {
    const productId = btn.getAttribute('data-product-id');
    if (!productId) {
        alert('Error: producto sin ID.');
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    fetch('../backend/cart/add_to_cart.php', { // desde /html/*.php
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            alert(data.msg); // luego se puede cambiar por un toast
        } else {
            alert(data.msg || 'No se pudo agregar al carrito.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error de conexión con el servidor.');
    });
}

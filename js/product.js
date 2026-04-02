document.addEventListener('DOMContentLoaded', () => {
    const priceDisplay = document.getElementById('display-price');
    const radios = document.querySelectorAll('input[name="variant_id"]');
    const qtyInput = document.getElementById('qty-input');
    const stockWarning = document.getElementById('stock-warning');

    if (radios.length > 0 && priceDisplay) {
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const price = this.getAttribute('data-price');
                const stock = parseInt(this.getAttribute('data-stock'), 10);
                
                priceDisplay.textContent = '฿' + new Intl.NumberFormat().format(price);
                
                if (!isNaN(stock) && qtyInput) {
                    qtyInput.max = stock;
                    if (parseInt(qtyInput.value, 10) > stock) {
                        qtyInput.value = stock;
                    }
                    if (stock === 0) {
                        stockWarning.textContent = "Out of stock";
                        stockWarning.style.display = "inline";
                    } else {
                        stockWarning.style.display = "none";
                    }
                }
            });
        });
    }
    
    if (qtyInput && stockWarning) {
        // Add event listener to qty input to show warning if user types manually
        qtyInput.addEventListener('input', function() {
            const max = parseInt(this.max, 10);
            const current = parseInt(this.value, 10);
            
            if (!isNaN(max) && current > max) {
                this.value = max;
                stockWarning.textContent = `Only ${max} items available`;
                stockWarning.style.display = "inline";
            } else {
                stockWarning.style.display = "none";
            }
        });
    }
});

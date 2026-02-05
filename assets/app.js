document.addEventListener('DOMContentLoaded', () => {
    const itemsBody = document.getElementById('itemsBody');
    const commentsList = document.getElementById('commentsList');
    const invoiceForm = document.getElementById('invoiceForm');
    const taxRateInput = document.getElementById('taxRate');
    const otherAmountInput = document.getElementById('otherAmount');
    const addCommentBtn = document.getElementById('addComment');
    const addRowBtn = document.getElementById('addRow');
    const clearFormBtn = document.getElementById('clearForm');
    
    const modal = document.getElementById('customAlert');
    const modalTitle = modal.querySelector('h3');
    const modalMessage = document.getElementById('modalMessage');
    const closeModalBtn = document.getElementById('closeModal');

    const CHAR_LIMIT = 100;
    let isDataSaved = false;

    history.pushState(null, null, window.location.href);
    window.onpopstate = function () {
        if (!isDataSaved) {
            showModal("Discard Changes?", "You have unsaved changes. Are you sure you want to leave?", true, () => {
                isDataSaved = true;
                history.back();
            });
            history.pushState(null, null, window.location.href);
        }
    };

    function showModal(title, message, isConfirm = false, onConfirm = null) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        const oldConfirm = modal.querySelector('.btn-modal-danger');
        if (oldConfirm) oldConfirm.remove();

        if (isConfirm) {
            closeModalBtn.textContent = "Cancel";
            const confirmBtn = document.createElement('button');
            confirmBtn.textContent = "Yes, Proceed";
            confirmBtn.className = "btn-modal-danger";
            confirmBtn.onclick = () => { isDataSaved = true; onConfirm(); modal.style.display = 'none'; };
            closeModalBtn.parentNode.appendChild(confirmBtn);
        } else {
            closeModalBtn.textContent = "OK";
        }
        modal.style.display = 'flex';
    }

    function validateDueDate() {
        const dueDateInput = document.getElementById('due_date');
        const selectedDate = new Date(dueDateInput.value);
        const minAllowed = new Date();
        minAllowed.setDate(minAllowed.getDate() + 3);
        minAllowed.setHours(0, 0, 0, 0);

        if (selectedDate < minAllowed) {
            dueDateInput.classList.add('input-error');
            return false;
        } else {
            dueDateInput.classList.remove('input-error');
            return true;
        }
    }

    async function initPrepopulation() {
        try {
            const billToInputs = document.querySelectorAll('.bill-to-inputs input, .bill-to-inputs textarea');
            billToInputs.forEach(input => {
                input.addEventListener('input', () => input.classList.remove('input-error'));
            });

            const response = await fetch('api/invoice.php');
            const settings = await response.json();
            if (!settings || settings.status === 'error') return;

            document.querySelector('.brand-name').textContent = settings.company_name;
            document.getElementById('payable_to_display').textContent = settings.company_name;
            
            const addressHTML = `
                ${(settings.company_address || '').replace(/\n/g, '<br>')}
                ${settings.company_phone ? '<br>Phone: ' + settings.company_phone : ''}
                ${settings.company_fax ? '<br>Fax: ' + settings.company_fax : ''}
                ${settings.company_website ? '<br>Website: ' + settings.company_website : ''}
            `.trim();
            
            document.getElementById('company_address_display').innerHTML = addressHTML;
            document.getElementById('contact_info_display').textContent = settings.contact_info;
            document.getElementById('invoice_number').value = settings.current_invoice_number;
            document.getElementById('customer_id').value = settings.current_customer_id;
            document.getElementById('currency').innerHTML = settings.current_currency;
            
            if (settings.current_tax_rate) {
                taxRateInput.value = parseFloat(settings.current_tax_rate).toFixed(3);
            }

            document.getElementById('invoice_date').value = new Date().toISOString().split('T')[0];

            const dueDateInput = document.getElementById('due_date');
            const minFutureDate = new Date();
            minFutureDate.setDate(minFutureDate.getDate() + 3);
            const minDateString = minFutureDate.toISOString().split('T')[0];

            dueDateInput.min = minDateString;
            dueDateInput.value = minDateString;
            dueDateInput.addEventListener('change', validateDueDate);
            
            updateTotals();
        } catch (err) {
            console.error("Prepopulation Error:", err);
        }
    }

    function addRow() {
        const tr = document.createElement('tr');
        tr.className = 'invoice-row';
        tr.innerHTML = `
            <td class="col-desc"><input type="text" placeholder="[Description]"></td>
            <td style="text-align:center"><input type="checkbox" class="tax-check"></td>
            <td><input type="number" class="price" step="0.01" value="0.00" style="text-align:right;"></td>
            <td class="no-print" style="text-align:center"><button type="button" class="remove-row">&#128465;</button></td>
        `;
        itemsBody.appendChild(tr);

        tr.querySelector('.price').addEventListener('input', updateTotals);
        tr.querySelector('.tax-check').addEventListener('change', updateTotals);
        tr.querySelector('.col-desc input').addEventListener('input', (e) => e.target.classList.remove('input-error'));
        tr.querySelector('.remove-row').addEventListener('click', () => {
            if (document.querySelectorAll('.invoice-row').length > 1) {
                tr.remove();
                updateTotals();
            } else {
                showModal("Required", "An invoice must have at least one line item.");
            }
        });
    }

    addCommentBtn.addEventListener('click', () => {
        const currentExtraFields = commentsList.querySelectorAll('.comment-input').length;
        if (currentExtraFields < 3) {
            const li = document.createElement('li');
            li.className = 'comment-wrapper';
            li.innerHTML = `
                <input type="text" class="comment-input" maxlength="${CHAR_LIMIT}" placeholder="[Additional comment...]">
                <span class="char-counter">${CHAR_LIMIT} chars left</span>
            `;
            commentsList.appendChild(li);
            
            const input = li.querySelector('.comment-input');
            const counter = li.querySelector('.char-counter');
            input.addEventListener('input', () => {
                const remaining = CHAR_LIMIT - input.value.length;
                counter.textContent = `${remaining} chars left`;
                counter.classList.toggle('limit-reached', remaining <= 0);
            });
            if (currentExtraFields === 2) addCommentBtn.style.display = 'none';
        }
    });

    function updateTotals() {
        let subtotal = 0;
        let taxableSum = 0;

        document.querySelectorAll('.invoice-row').forEach(row => {
            const priceVal = parseFloat(row.querySelector('.price').value) || 0;
            subtotal += priceVal;
            if (row.querySelector('.tax-check').checked) taxableSum += priceVal;
        });

        const taxRate = parseFloat(taxRateInput.value) / 100 || 0;
        const otherAmount = parseFloat(otherAmountInput.value) || 0;
        const calculatedTax = taxableSum * taxRate;
        const totalValue = subtotal + calculatedTax + otherAmount;

        document.getElementById('subTotal').textContent = subtotal.toFixed(2);
        document.getElementById('taxableAmt').textContent = calculatedTax.toFixed(2);
        document.getElementById('grandTotal').textContent = totalValue.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    invoiceForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        let isValid = true;

        const billToInputs = document.querySelectorAll('.bill-to-inputs input, .bill-to-inputs textarea');
        billToInputs.forEach(el => {
            if (!el.value.trim()) { el.classList.add('input-error'); isValid = false; } 
            else { el.classList.remove('input-error'); }
        });

        if (!validateDueDate()) {
            showModal("Invalid Date", "The Due Date must be at least 3 days in the future.");
            return;
        }

        document.querySelectorAll('.invoice-row').forEach(row => {
            const descInput = row.querySelector('.col-desc input');
            const priceInput = row.querySelector('.price');
            if (!descInput.value.trim()) { descInput.classList.add('input-error'); isValid = false; }
            if (!priceInput.value || parseFloat(priceInput.value) <= 0) { priceInput.classList.add('input-error'); isValid = false; }
        });

        if (!isValid) {
            showModal("Missing Information", "All highlighted fields are compulsory. Ensure money values are greater than zero.");
            return;
        }

        const submitBtn = e.target.querySelector('.btn-primary');
        submitBtn.disabled = true;
        submitBtn.textContent = "Saving...";

        const payload = {
            header: {
                invoice_number: document.getElementById('invoice_number').value,
                invoice_date: document.getElementById('invoice_date').value,
                due_date: document.getElementById('due_date').value,
                customer_id: document.getElementById('customer_id').value,
                customer_name: document.getElementById('customer_name').value,
                customer_address: document.getElementById('customer_address').value,
                subtotal: document.getElementById('subTotal').textContent,
                tax_total: document.getElementById('taxableAmt').textContent,
                grand_total: document.getElementById('grandTotal').textContent
            },
            items: Array.from(document.querySelectorAll('.invoice-row')).map(row => ({
                description: row.querySelector('.col-desc input').value,
                is_taxed: row.querySelector('.tax-check').checked ? 1 : 0,
                amount: row.querySelector('.price').value
            }))
        };

        try {
            const res = await fetch('api/invoice.php', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload) 
            });
            const result = await res.json();

            if (res.ok) {
                isDataSaved = true;
                showModal("Success", "Invoice saved successfully!");
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showModal("Error", result.message || "Failed to save invoice.");
            }
        } catch (err) {
            showModal("Error", "Network error occurred.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Save Invoice Data";
        }
    });

    closeModalBtn.addEventListener('click', () => modal.style.display = 'none');
    addRowBtn.addEventListener('click', addRow);
    taxRateInput.addEventListener('input', updateTotals);
    otherAmountInput.addEventListener('input', updateTotals);
    
    clearFormBtn.addEventListener('click', () => {
        showModal("Confirm Reset", "Are you sure? This will reset all fields.", true, () => {
            isDataSaved = true;
            window.location.reload();
        });
    });

    initPrepopulation();
    for (let i=0; i < 5; i++) addRow();
});

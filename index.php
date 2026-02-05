<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>d6 - Tax Invoice</title>
        <link rel="icon" type="image/png" href="./assets/d6.png" />
        <link rel="stylesheet" href="assets/style.css" />
    </head>

    <body>
        <div class="container invoice-box">
            <div class="invoice-top">
                <div class="company-info">
                    <h1 class="brand-name"></h1>
                    <div class="editable-address">
                        <p id="company_address_display"></p>
                    </div>
                </div>
                <div class="invoice-meta">
                    <h2 class="title-invoice">INVOICE</h2>
                    <table class="meta-table">
                        <tr><td>DATE</td><td><input type="date" id="invoice_date" readonly tabindex="-1" class="locked-input" /></td></tr>
                        <tr><td>INVOICE #</td><td><input type="text" id="invoice_number" readonly tabindex="-1" class="locked-input" /></td></tr>
                        <tr><td>CUSTOMER ID</td><td><input type="text" id="customer_id" readonly tabindex="-1" class="locked-input" /></td></tr>
                        <tr><td>DUE DATE</td><td><input type="date" id="due_date" /></td></tr>
                    </table>
                </div>
            </div>

            <form id="invoiceForm" novalidate>
                <div class="section-banner">BILL TO</div>
                <div class="bill-to-inputs">
                    <div class="input-wrapper">
                        <input type="text" id="customer_name" placeholder="[Name]" required />
                        <span class="error-msg" id="err-customer_name"></span>
                    </div>
                    <div class="input-wrapper">
                        <input type="text" id="customer_company" placeholder="[Company Name]" required />
                        <span class="error-msg" id="err-customer_company"></span>
                    </div>
                    <div class="input-wrapper">
                        <textarea id="customer_address" placeholder="[Street Address]&#10;[City, ST ZIP]" rows="2" required></textarea>
                        <span class="error-msg" id="err-customer_address"></span>
                    </div>
                    <div class="input-wrapper">
                        <input type="text" id="customer_phone" placeholder="[Phone]" required />
                        <span class="error-msg" id="err-customer_phone"></span>
                    </div>
                </div>

                <div class="table-container">
                    <table id="itemsTable">
                        <thead>
                            <tr class="table-header-row">
                                <th class="col-desc">DESCRIPTION</th>
                                <th class="col-taxed" width="60">TAXED</th>
                                <th class="col-amount" width="120">AMOUNT</th>
                                <th class="col-action no-print" width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                    <div class="table-footer-actions no-print">
                        <button type="button" id="addRow" class="btn-add-item">+ Add Line Item</button>
                    </div>
                </div>

                <div class="invoice-bottom">
                    <div class="bottom-left">
                        <div class="section-banner">OTHER COMMENTS</div>
                        <div class="comments-content border-outline">
                            <ol id="commentsList">
                                <li>Total payment due in 30 days.</li>
                                <li>Please include the invoice number on your cheque.</li>
                            </ol>
                            <button type="button" id="addComment" class="no-print btn-small-link">+ Add Comment</button>
                        </div>
                    </div>

                    <div class="bottom-right">
                        <table class="totals-table">
                            <tr><td>Subtotal</td><td id="subTotal">0.00</td></tr>
                            <tr><td>Taxable</td><td id="taxableAmt">0.00</td></tr>
                            <tr>
                                <td>Tax rate</td>
                                <td class="tax-rate-label" style="text-align: right;">
                                    <input type="number" id="taxRate" value="" step="1.250" /> %
                                </td>
                            </tr>
                            <tr><td>Other</td><td class="input-cell"><input type="number" id="otherAmount" value="0.00" step="0.100" /></td></tr>
                            <tr class="grand-total-row"><td>TOTAL</td><td><span id="currency"></span> <span id="grandTotal">0.00</span></td></tr>
                        </table>
                        
                        <div class="payment-info centered-info">
                            <p>Make all cheques payable to</p>
                            <strong id="payable_to_display"></strong>
                        </div>
                    </div>
                </div>

                <footer class="final-footer centered-info">
                    <p>If you have any questions about this invoice, please contact</p>
                    <p id="contact_info_display"></p>
                    <p class="thanks-msg">Thank You For Your Business!</p>
                </footer>

                <div class="form-actions no-print">
                    <div class="action-footer-wrapper">
                        <img src="assets/d6.png" alt="d6 Logo" class="footer-logo">
                        <div class="footer-buttons">
                            <button type="button" onclick="window.print()" class="btn-print">Print / PDF</button>
                            <button type="button" class="btn-clear" id="clearForm">Clear All</button>
                            <button type="submit" class="btn-primary">Save Invoice Data</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div id="customAlert" class="modal-overlay">
            <div class="modal-content">
                <h3>Required Information</h3>
                <p id="modalMessage"></p>
                <button id="closeModal">OK</button>
            </div>
        </div>

        <script type="text/javascript" src="assets/app.js"></script>
    </body>
</html>
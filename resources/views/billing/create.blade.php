@extends('layouts.shell', ['active' => 'orders'])

@section('title', 'New Order | Store Inventory')

@section('page')
<div>
    <div class="mb-4">
        <h1 class="page-title mb-1">New Order</h1>
        <p class="page-sub mb-0">Add products, take payment, and generate the bill.</p>
    </div>

    <div id="alert-box" class="alert d-none" role="alert"></div>

    <form id="billing-form" autocomplete="off">
        @csrf

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="panel p-3 p-md-4 mb-3">
                    <h2 class="h6 mb-3">Customer</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_email" class="form-label">Email</label>
                            <input id="customer_email" type="email" class="form-control" placeholder="e.g. thomas@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label">Name</label>
                            <input id="customer_name" type="text" class="form-control" placeholder="Auto-filled if email exists" required>
                        </div>
                    </div>
                </div>

                <div class="panel p-3 p-md-4 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h6 mb-0">Products</h2>
                        <button type="button" id="add-item" class="btn btn-sm btn-quiet">+ Add Product</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-clean align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th style="width: 90px;">Qty</th>
                                    <th class="text-end" style="width: 110px;">Price</th>
                                    <th class="text-end" style="width: 130px;">Line Total</th>
                                    <th style="width: 44px;"></th>
                                </tr>
                            </thead>
                            <tbody id="item-rows"></tbody>
                        </table>
                    </div>
                </div>

                <div class="panel p-3 p-md-4">
                    <h2 class="h6 mb-3">Payment</h2>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span id="subtotal-display">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax</span>
                        <span id="tax-display">₹0.00</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Grand Total</span>
                        <span id="grand-total-display">₹0.00</span>
                    </div>
                    <label for="amount_given" class="form-label">Amount given by customer</label>
                    <input id="amount_given" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
                    <div class="d-flex justify-content-between mt-3">
                        <span class="text-muted">Balance to return</span>
                        <span id="change-display" class="text-end">₹0.00</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel p-3 p-md-4 mb-3">
                    <h2 class="h6 mb-3">Low stock</h2>
                    @if ($lowStockProducts->isEmpty())
                        <p class="page-sub mb-0">No items are below {{ $lowStockThreshold }} units.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($lowStockProducts as $product)
                                <li class="d-flex justify-content-between align-items-center py-1">
                                    <span>{{ $product->name }}</span>
                                    <span class="stock-badge">{{ $product->stock_quantity }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <button type="submit" id="generate-bill" class="btn btn-brand w-100 py-2">Generate Bill</button>
                <p class="page-sub mt-2 mb-0">Saves the order and shows the bill below.</p>
            </div>
        </div>
    </form>

    <div id="bill-receipt" class="panel p-3 p-md-4 mt-3">
        <h2 class="h6">Bill</h2>
        <p class="mb-1"><strong>Bill #</strong> <span id="receipt-id"></span></p>
        <p class="mb-1"><strong>Customer:</strong> <span id="receipt-customer"></span></p>
        <p class="mb-3"><strong>Date:</strong> <span id="receipt-date"></span></p>
        <div class="table-responsive">
            <table class="table table-clean table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody id="receipt-items"></tbody>
            </table>
        </div>
        <div class="text-end">
            <div>Subtotal: <span id="receipt-subtotal"></span></div>
            <div>Tax: <span id="receipt-tax"></span></div>
            <div class="fw-bold">Grand Total: <span id="receipt-grand"></span></div>
            <div>Amount given: <span id="receipt-given"></span></div>
            <div>Balance: <span id="receipt-change"></span></div>
        </div>
    </div>
</div>

<template id="item-row-template">
    <tr class="item-row">
        <td>
            <select class="form-select product-select" required>
                <option value="">Select product</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control qty-input" min="1" step="1" value="1" required>
        </td>
        <td class="text-end unit-price">₹0.00</td>
        <td class="text-end line-total">₹0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-quiet remove-row" title="Remove">&times;</button>
        </td>
    </tr>
</template>
@endsection

@section('page-scripts')
<script>
(function () {
    var products = @json($products);
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var rowsEl = document.getElementById('item-rows');
    var template = document.getElementById('item-row-template');
    var form = document.getElementById('billing-form');
    var alertBox = document.getElementById('alert-box');

    function money(value) {
        return '₹' + Number(value).toFixed(2);
    }

    function findProduct(id) {
        id = parseInt(id, 10);
        for (var i = 0; i < products.length; i++) {
            if (parseInt(products[i].id, 10) === id) {
                return products[i];
            }
        }
        return null;
    }

    function lineTotals(product, qty) {
        var subtotal = roundMoney(parseFloat(product.price) * qty);
        var tax = roundMoney(subtotal * parseFloat(product.tax_rate) / 100);
        return {
            subtotal: subtotal,
            tax: tax,
            total: roundMoney(subtotal + tax)
        };
    }

    function roundMoney(value) {
        return Math.round(value * 100) / 100;
    }

    function denomination(amount) {
        var remaining = Math.round(amount * 100);
        var values = [200000, 100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100, 50, 20, 10];
        var parts = [];
        for (var i = 0; i < values.length; i++) {
            var count = Math.floor(remaining / values[i]);
            if (count > 0) {
                var label = values[i] >= 100 ? String(values[i] / 100) : (values[i] / 100).toFixed(2);
                parts.push(count + '×' + label);
                remaining -= count * values[i];
            }
        }
        if (!parts.length) {
            return money(amount);
        }
        return money(amount) + ' → ' + parts.join(' + ');
    }

    function showAlert(message, type) {
        alertBox.className = 'alert alert-' + type;
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    }

    function hideAlert() {
        alertBox.classList.add('d-none');
    }

    function addRow() {
        var node = template.content.cloneNode(true);
        var select = node.querySelector('.product-select');
        products.forEach(function (product) {
            var option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name + ' (' + product.sku + ')';
            select.appendChild(option);
        });
        rowsEl.appendChild(node);
        bindRow(rowsEl.lastElementChild);
        recalculate();
    }

    function bindRow(row) {
        row.querySelector('.product-select').addEventListener('change', recalculate);
        row.querySelector('.qty-input').addEventListener('input', recalculate);
        row.querySelector('.remove-row').addEventListener('click', function () {
            if (rowsEl.children.length === 1) {
                row.querySelector('.product-select').value = '';
                row.querySelector('.qty-input').value = 1;
                recalculate();
                return;
            }
            row.remove();
            recalculate();
        });
    }

    function currentItems() {
        var items = [];
        Array.prototype.forEach.call(rowsEl.querySelectorAll('.item-row'), function (row) {
            var product = findProduct(row.querySelector('.product-select').value);
            var qty = parseInt(row.querySelector('.qty-input').value, 10) || 0;
            if (!product || qty < 1) {
                row.querySelector('.unit-price').textContent = money(0);
                row.querySelector('.line-total').textContent = money(0);
                return;
            }
            var totals = lineTotals(product, qty);
            row.querySelector('.unit-price').textContent = money(product.price);
            row.querySelector('.line-total').textContent = money(totals.total);
            items.push({
                product: product,
                quantity: qty,
                totals: totals
            });
        });
        return items;
    }

    function recalculate() {
        var items = currentItems();
        var subtotal = 0;
        var tax = 0;
        items.forEach(function (item) {
            subtotal += item.totals.subtotal;
            tax += item.totals.tax;
        });
        subtotal = roundMoney(subtotal);
        tax = roundMoney(tax);
        var grand = roundMoney(subtotal + tax);
        document.getElementById('subtotal-display').textContent = money(subtotal);
        document.getElementById('tax-display').textContent = money(tax);
        document.getElementById('grand-total-display').textContent = money(grand);

        var given = parseFloat(document.getElementById('amount_given').value);
        if (isNaN(given) || given <= 0) {
            document.getElementById('change-display').textContent = money(0);
            return grand;
        }
        var change = roundMoney(given - grand);
        if (change < 0) {
            document.getElementById('change-display').textContent = 'Short ' + money(Math.abs(change));
        } else {
            document.getElementById('change-display').textContent = denomination(change);
        }
        return grand;
    }

    document.getElementById('add-item').addEventListener('click', addRow);
    document.getElementById('amount_given').addEventListener('input', recalculate);

    var emailInput = document.getElementById('customer_email');
    var nameInput = document.getElementById('customer_name');
    var lookupTimer = null;

    emailInput.addEventListener('input', function () {
        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(lookupCustomer, 400);
    });
    emailInput.addEventListener('blur', lookupCustomer);

    function lookupCustomer() {
        var email = emailInput.value.trim();
        if (!email || email.indexOf('@') === -1) {
            return;
        }
        fetch('/customers/lookup?email=' + encodeURIComponent(email), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        }).then(function (response) {
            return response.json();
        }).then(function (payload) {
            if (payload.data && payload.data.name) {
                nameInput.value = payload.data.name;
            }
        }).catch(function () {});
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        hideAlert();

        var items = currentItems().map(function (item) {
            return { product_id: item.product.id, quantity: item.quantity };
        });

        if (!items.length) {
            showAlert('Add at least one product.', 'danger');
            return;
        }

        var grand = recalculate();
        var given = parseFloat(document.getElementById('amount_given').value);
        if (isNaN(given) || given < grand) {
            showAlert('Amount given must be at least the grand total.', 'danger');
            return;
        }

        var button = document.getElementById('generate-bill');
        button.disabled = true;

        fetch('/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({
                customer_name: nameInput.value.trim(),
                customer_email: emailInput.value.trim(),
                items: items,
                amount_given: given
            })
        }).then(function (response) {
            return response.json().then(function (payload) {
                payload.status = response.status;
                return payload;
            });
        }).then(function (payload) {
            button.disabled = false;
            if (payload.status === 201) {
                showAlert('Bill generated and saved.', 'success');
                renderReceipt(payload.data);
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                return;
            }
            var message = payload.message || 'Unable to generate the bill.';
            if (payload.errors) {
                var firstKey = Object.keys(payload.errors)[0];
                if (firstKey && payload.errors[firstKey][0]) {
                    message = payload.errors[firstKey][0];
                }
            }
            showAlert(message, 'danger');
        }).catch(function () {
            button.disabled = false;
            showAlert('Unable to generate the bill.', 'danger');
        });
    });

    function renderReceipt(order) {
        document.getElementById('bill-receipt').style.display = 'block';
        document.getElementById('receipt-id').textContent = order.id;
        document.getElementById('receipt-customer').textContent = order.customer.name + ' (' + order.customer.email + ')';
        document.getElementById('receipt-date').textContent = order.created_at || '';
        document.getElementById('receipt-subtotal').textContent = money(order.subtotal);
        document.getElementById('receipt-tax').textContent = money(order.tax_amount);
        document.getElementById('receipt-grand').textContent = money(order.total_amount);
        document.getElementById('receipt-given').textContent = money(order.amount_given || 0);
        document.getElementById('receipt-change').textContent = order.change_breakdown
            ? order.change_breakdown.summary
            : money(order.change_due || 0);

        var tbody = document.getElementById('receipt-items');
        tbody.innerHTML = '';
        (order.items || []).forEach(function (item) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + (item.product && item.product.name ? item.product.name : '') + '</td>' +
                '<td class="text-end">' + item.quantity + '</td>' +
                '<td class="text-end">' + money(item.unit_price) + '</td>' +
                '<td class="text-end">' + money(item.total_amount) + '</td>';
            tbody.appendChild(tr);
        });
    }

    addRow();
})();
</script>
@endsection

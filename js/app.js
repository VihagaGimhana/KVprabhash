$(document).ready(function() {

    // 1. MOBILE NAVBAR TOGGLE
    $('#mobileMenuBtn').on('click', function() {
        $('.nav-menu').toggleClass('active');
        $(this).find('i').toggleClass('fa-bars-staggered fa-xmark');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('header').length) {
            $('.nav-menu').removeClass('active');
            $('#mobileMenuBtn').find('i').removeClass('fa-xmark').addClass('fa-bars-staggered');
        }
    });

    // 2. LIVE MENU FILTERING & ORDER MANAGEMENT SYSTEM
    if ($('#menuDisplayGrid').length) {
        let currentCategory = 'all';
        let cart = [];

        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            currentCategory = $(this).data('category');
            applyMenuFilters();
        });

        $('#dietaryGroup').find('input[type="checkbox"]').on('change', applyMenuFilters);

        function applyMenuFilters() {
            let checkedDiets = [];
            $('#dietaryGroup').find('input[type="checkbox"]:checked').each(function() {
                checkedDiets.push($(this).val());
            });

            $('.menu-item-row').each(function() {
                let $item = $(this);
                let itemCategory = $item.data('category');
                let itemTags = ($item.data('tags') || '').split(' ').filter(t => t.length > 0);

                let matchesCategory = (currentCategory === 'all' || itemCategory === currentCategory);
                let matchesDiet = true;

                for (let i = 0; i < checkedDiets.length; i++) {
                    if (!itemTags.includes(checkedDiets[i])) {
                        matchesDiet = false;
                        break;
                    }
                }

                if (matchesCategory && matchesDiet) {
                    $item.fadeIn(300);
                } else {
                    $item.fadeOut(200);
                }
            });
        }

        // --- VISIBLE CHECKOUT CART MOTOR ---
        $('.btn-add-cart').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const price = parseFloat($(this).data('price'));

            let existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.qty++;
            } else {
                cart.push({ id: id, title: title, price: price, qty: 1 });
            }
            updateCartUI();
        });

        function updateCartUI() {
            const $cartList = $('#cartItemsList');
            $cartList.empty();
            let grandTotal = 0;

            if (cart.length === 0) {
                $cartList.html('<p style="color: var(--color-text-muted); text-align:center;">Your cart is empty. Click "Add to Order" to add delicious food!</p>');
                $('#cartTotalDisplay').text('Rs. 0');
                return;
            }

            cart.forEach(item => {
                let itemTotal = item.price * item.qty;
                grandTotal += itemTotal;
                $cartList.append(`
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:0.5rem;">
                        <div style="flex-grow:1;">
                            <h4 style="font-size:1rem; color:var(--color-text-light);">${item.title}</h4>
                            <small style="color:var(--color-gold);">Rs. ${item.price.toLocaleString()} x ${item.qty}</small>
                        </div>
                        <div style="font-weight:bold; color:var(--color-text-light);">Rs. ${itemTotal.toLocaleString()}</div>
                    </div>
                `);
            });

            $('#cartTotalDisplay').text('Rs. ' + grandTotal.toLocaleString());
        }

        $('#btnClearCart').on('click', function() {
            cart = [];
            updateCartUI();
        });

        $('#foodOrderForm').on('submit', function(e) {
            e.preventDefault();

            if (cart.length === 0) {
                $('#orderAlert').removeClass('live-alert-success').addClass('live-alert-error')
                    .text('කරුණාකර ඇණවුම් කිරීමට ප්‍රථමයෙන් කෑම වර්ගයක් එකතු කරන්න.').slideDown(200);
                return;
            }

            let grandTotal = 0;
            cart.forEach(item => grandTotal += (item.price * item.qty));

            const orderData = {
                name: $('#customerName').val(),
                email: $('#customerEmail').val(),
                items: cart,
                total: grandTotal
            };

            $.ajax({
                url: 'api/order.php',
                type: 'POST',
                data: JSON.stringify(orderData),
                contentType: 'application/json',
                success: function(res) {
                    $('#orderAlert').removeClass('live-alert-error').addClass('live-alert-success').text(res.message).slideDown(200);
                    cart = [];
                    updateCartUI();
                    $('#foodOrderForm')[0].reset();
                },
                error: function(xhr) {
                    $('#orderAlert').removeClass('live-alert-success').addClass('live-alert-error').text(xhr.responseJSON?.message || 'Error executing transactional request.').slideDown(200);
                }
            });
        });
    }

    // 3. SOMMELIER WINE ASSISTANT
    if ($('#wineEntreeSelect').length) {
        const wineDatabase = {
            scallops: { name: "Chablis Grand Cru 'Les Preuses'", desc: "Crisp white wine with clean citrus zest that mirrors scallops flawlessly." },
            duck: { name: "Pinot Noir Reserve Albert Mann", desc: "Silky red tannins balancing wild cherries to cut through duck fat cleanly." },
            trout: { name: "Pouilly-Fuissé 'Tête de Cru'", desc: "Rich buttery white profile matching a pan-seared brown butter preparation." },
            ribeye: { name: "Château Margaux Grand Cru Classé", desc: "Crown jewel Cabernet blend with deep velvety profiles for marbled beef." }
        };

        $('#wineEntreeSelect').on('change', function() {
            const val = $(this).val();
            const $resultBox = $('#wineResultBox');

            $resultBox.fadeOut(200, function() {
                if (val && wineDatabase[val]) {
                    $('#wineRecName').text(wineDatabase[val].name);
                    $('#wineRecDesc').text(wineDatabase[val].desc);
                    $resultBox.fadeIn(300);
                }
            });
        });
    }

    // 4. RESERVATION FLOOR SCHEDULER
    if ($('#reservationForm').length) {
        const today = new Date().toISOString().split('T')[0];
        $('#bookDate').attr('min', today).val(today);

        fetchTableOccupancy();
        $('#bookDate, #bookTime').on('change', fetchTableOccupancy);

        function fetchTableOccupancy() {
            const date = $('#bookDate').val();
            const time = $('#bookTime').val();
            if (!date || !time) return;

            $('.table-seat').removeClass('booked selected');
            $('#bookTableId').val('');
            $('#bookTableDisplay').val('');
            $('#bookingAlert').slideUp(100);

            $.ajax({
                url: 'api/reserve.php',
                type: 'GET',
                data: { date: date, time: time },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success' && res.occupied) {
                        res.occupied.forEach(tId => $('#svgTable' + tId).addClass('booked'));
                    }
                }
            });
        }

        $('.table-seat').on('click', function() {
            if ($(this).hasClass('booked')) return;
            $('.table-seat').removeClass('selected');
            $(this).addClass('selected');

            const id = $(this).data('table-id');
            const cap = $(this).data('capacity');
            $('#bookTableId').val(id);
            $('#bookTableDisplay').val(`Table ${id} — Luxury seating for up to ${cap} individuals.`);
        });

        $('#reservationForm').on('submit', function(e) {
            e.preventDefault();
            if (!$('#bookTableId').val()) {
                showInlineAlert('error', 'කරුණාකර සිතියමෙන් මේසයක් තෝරාගන්න.');
                return;
            }

            $.ajax({
                url: 'api/reserve.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    showInlineAlert('success', res.message);
                    $('#reservationForm')[0].reset();
                    $('#bookDate').val(today);
                    fetchTableOccupancy();
                },
                error: function(xhr) {
                    showInlineAlert('error', xhr.responseJSON?.message || 'Error saving allocation.');
                }
            });
        });

        function showInlineAlert(type, msg) {
            $('#bookingAlert').removeClass('live-alert-success live-alert-error')
                .addClass('live-alert-' + type).text(msg).slideDown(200);
        }
    }

    // 5. CONTACT MESSAGE AJAX
    if ($('#contactForm').length) {
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/contact.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    $('#contactAlert').removeClass('live-alert-error').addClass('live-alert-success').text(res.message).slideDown(200);
                    $('#contactForm')[0].reset();
                },
                error: function(xhr) {
                    $('#contactAlert').removeClass('live-alert-success').addClass('live-alert-error').text(xhr.responseJSON?.message || 'Error processing context.').slideDown(200);
                }
            });
        });
    }
});
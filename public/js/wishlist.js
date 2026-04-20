/**
 * CampusMarket wishlist — localStorage. On wishlist page, use heart / Remove to undo a like.
 */
(function () {
  var KEY = 'campusmarket_wishlist_v1';

  function parseWishlist() {
    try {
      var raw = localStorage.getItem(KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  }

  window.getWishlist = function () {
    var w = parseWishlist();
    return Array.isArray(w) ? w : [];
  };

  window.saveWishlist = function (items) {
    localStorage.setItem(KEY, JSON.stringify(items));
  };

  window.toggleWishlist = function (id, item) {
    var w = getWishlist();
    var n = Number(id);
    var idx = w.findIndex(function (x) {
      return Number(x.id) === n;
    });
    if (idx >= 0) {
      w.splice(idx, 1);
    } else {
      w.push(item && typeof item === 'object' ? item : { id: n });
    }
    saveWishlist(w);
    updateWishlistUI();
  };

  /** Remove (or toggle) using only id — used on wishlist page so we do not embed JSON in HTML. */
  window.toggleWishlistFromStorage = function (id) {
    var n = Number(id);
    var w = getWishlist();
    var item = w.find(function (x) {
      return Number(x.id) === n;
    });
    toggleWishlist(n, item || { id: n });
  };

  function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function formatPriceDisplay(p) {
    var raw = p && p.price;
    var n = typeof raw === 'number' ? raw : parseFloat(String(raw != null ? raw : '').replace(/,/g, ''), 10);
    if (!isFinite(n)) return '';
    return n.toLocaleString('tr-TR') + ' TL';
  }

  function renderWishlistContainer(container, items) {
    if (!items.length) {
      container.innerHTML =
        '<p class="wishlist-empty-msg">No saved items yet. Tap the heart on a listing to add it here.</p>';
      return;
    }
    var parts = ['<div class="wishlist-grid">'];
    items.forEach(function (p) {
      var pid = Number(p.id);
      var title = escapeHtml(p.title || 'Item');
      var cat = escapeHtml(p.category || '');
      var price = escapeHtml(formatPriceDisplay(p));
      var img = escapeHtml(p.img || '');
      parts.push(
        '<article class="wishlist-item" tabindex="0" role="link" data-href="product.php?id=' +
          pid +
          '">' +
          '<img class="wishlist-item-img" src="' +
          img +
          '" alt="' +
          title +
          '">' +
          '<div class="wishlist-item-body">' +
          '<div class="wishlist-item-title">' +
          title +
          '</div>' +
          '<div class="wishlist-item-meta">' +
          cat +
          ' · ' +
          price +
          '</div>' +
          '<div class="wishlist-item-actions">' +
          '<button type="button" class="wishlist-unlike-btn" id="heart-' +
          pid +
          '" onclick="event.stopPropagation();toggleWishlistFromStorage(' +
          pid +
          ')" aria-label="Remove from wishlist">' +
          '<span aria-hidden="true">♥</span> Remove' +
          '</button>' +
          '</div></div></article>'
      );
    });
    parts.push('</div>');
    container.innerHTML = parts.join('');

    container.querySelectorAll('.wishlist-item[data-href]').forEach(function (el) {
      function go() {
        window.location.href = el.getAttribute('data-href');
      }
      el.addEventListener('click', function (e) {
        if (e.target.closest('.wishlist-unlike-btn')) return;
        go();
      });
      el.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          go();
        }
      });
    });
  }

  window.updateWishlistUI = function () {
    var w = getWishlist();
    var ids = {};
    w.forEach(function (x) {
      ids[Number(x.id)] = true;
    });

    document.querySelectorAll('.heart-btn').forEach(function (btn) {
      var m = btn.id && btn.id.match(/^heart-(\d+)$/);
      if (!m) return;
      var id = parseInt(m[1], 10);
      if (ids[id]) {
        btn.classList.add('active');
        btn.setAttribute('aria-pressed', 'true');
      } else {
        btn.classList.remove('active');
        btn.setAttribute('aria-pressed', 'false');
      }
    });

    var container = document.getElementById('wishlist-container');
    if (container) {
      renderWishlistContainer(container, w);
    }
  };
})();

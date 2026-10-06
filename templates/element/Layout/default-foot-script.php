        <script>
        window.fnsToast = window.fnsToast || function(msg, ms){
            var t = document.getElementById('fns_toast');
            if(!t) return;
            t.textContent = msg; t.style.display = 'block';
            clearTimeout(t._t); t._t = setTimeout(function(){ t.style.display = 'none'; }, ms || 2800);
        };
        </script>

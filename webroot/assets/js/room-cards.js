/* Room cards: photo slider, pills filter. Extracted from rooms.php element. */
window._roomPhotoIdx = window._roomPhotoIdx || {};
function selectRoomCard(roomId){
    document.querySelectorAll('.agoda-room-card').forEach(function(c){c.classList.remove('selected')});
    var card=document.getElementById('room_card_'+roomId);
    if(card) card.classList.add('selected');
}
function cycleRoomPhoto(roomId, photos, dir) {
    if (!photos || !photos.length) return;
    dir = (dir === -1) ? -1 : 1;
    var cur = window._roomPhotoIdx[roomId] || 0;
    var total = photos.length;
    var next = (cur + dir + total) % total;
    window._roomPhotoIdx[roomId] = next;
    var imgEl = document.getElementById('room_img_' + roomId);
    if (imgEl) {
        imgEl.src = photos[next];
    }
    var counter=document.getElementById('room_counter_'+roomId);
    if(counter) counter.textContent=(next+1)+'/'+photos.length;
    selectRoomCard(roomId);
}
// Swipe on room photos (mobile): left = next, right = previous
(function(){
    function photosOf(wrap){
        try { return JSON.parse(wrap.getAttribute('data-photos') || '[]'); } catch(e){ return []; }
    }
    document.addEventListener('touchstart', function(e){
        var wrap = e.target.closest ? e.target.closest('.room-hero-wrap') : null;
        if(!wrap || !wrap.hasAttribute('data-room-id')) return;
        wrap._swipeX = e.touches[0].clientX;
    }, {passive:true});
    document.addEventListener('touchend', function(e){
        var wrap = e.target.closest ? e.target.closest('.room-hero-wrap') : null;
        if(!wrap || wrap._swipeX === undefined) return;
        var dx = e.changedTouches[0].clientX - wrap._swipeX;
        wrap._swipeX = undefined;
        if(Math.abs(dx) < 36) return;
        var id = parseInt(wrap.getAttribute('data-room-id'), 10);
        cycleRoomPhoto(id, photosOf(wrap), dx < 0 ? 1 : -1);
    }, {passive:true});
})();




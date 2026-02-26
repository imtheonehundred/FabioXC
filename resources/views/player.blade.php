<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPTV Player</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: #111; color: #eee; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1rem; }
        .player-wrap { position: relative; background: #000; aspect-ratio: 16/9; max-height: 70vh; }
        .player-wrap video { width: 100%; height: 100%; }
        .sidebar { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
        @media (min-width: 768px) { .sidebar { grid-template-columns: 1fr 2fr; } }
        .list { background: #1a1a1a; border-radius: 8px; padding: 0.75rem; max-height: 400px; overflow-y: auto; }
        .list h3 { margin: 0 0 0.5rem; font-size: 0.9rem; }
        .list ul { list-style: none; padding: 0; margin: 0; }
        .list li { padding: 0.4rem 0; border-bottom: 1px solid #333; cursor: pointer; }
        .list li:hover { background: #252525; }
        .login { margin-bottom: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .login input { padding: 0.5rem; border-radius: 4px; border: 1px solid #444; background: #222; color: #eee; }
        .login button { padding: 0.5rem 1rem; border-radius: 4px; border: none; background: #0d6efd; color: #fff; cursor: pointer; }
        .login button:hover { background: #0b5ed7; }
        .error { color: #f66; margin-top: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>IPTV Player</h1>
        <div class="login">
            <input type="text" id="username" placeholder="Username" />
            <input type="password" id="password" placeholder="Password" />
            <button type="button" id="btnLogin">Login</button>
            <span class="error" id="loginError"></span>
        </div>
        <div id="loggedIn" style="display: none;">
            <div class="player-wrap">
                <video id="video" controls playsinline></video>
            </div>
            <div class="sidebar">
                <div class="list">
                    <h3>Live</h3>
                    <ul id="liveList"></ul>
                </div>
                <div class="list">
                    <h3>VOD</h3>
                    <ul id="vodList"></ul>
                </div>
            </div>
        </div>
    </div>
    <script>
        const API_BASE = '{{ url("/api/player") }}';
        let user = { username: '', password: '' };

        document.getElementById('btnLogin').addEventListener('click', login);

        async function login() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            document.getElementById('loginError').textContent = '';
            if (!username || !password) {
                document.getElementById('loginError').textContent = 'Enter username and password';
                return;
            }
            try {
                const r = await fetch(API_BASE + '/login?username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password));
                const data = await r.json();
                if (data.user_info && data.user_info.auth === 1) {
                    user = { username, password };
                    document.getElementById('loggedIn').style.display = 'block';
                    loadLive();
                    loadVod();
                } else {
                    document.getElementById('loginError').textContent = data.user_info?.message || 'Login failed';
                }
            } catch (e) {
                document.getElementById('loginError').textContent = 'Network error';
            }
        }

        async function loadLive() {
            const r = await fetch(API_BASE + '/get_live_categories');
            const cats = await r.json();
            const r2 = await fetch(API_BASE + '/get_live_streams');
            const streams = await r2.json();
            const ul = document.getElementById('liveList');
            ul.innerHTML = '';
            (streams || []).forEach(s => {
                const li = document.createElement('li');
                li.textContent = s.name || ('Stream ' + s.stream_id);
                li.onclick = () => playLive(s.stream_id);
                ul.appendChild(li);
            });
        }

        async function loadVod() {
            const r = await fetch(API_BASE + '/get_vod_categories');
            await r.json();
            const r2 = await fetch(API_BASE + '/get_vod_streams');
            const list = await r2.json();
            const ul = document.getElementById('vodList');
            ul.innerHTML = '';
            (list || []).forEach(v => {
                const li = document.createElement('li');
                li.textContent = v.name || ('VOD ' + v.stream_id);
                li.onclick = () => playVod(v.stream_id, v.container_extension || 'mp4');
                ul.appendChild(li);
            });
        }

        function playLive(streamId) {
            const base = '{{ url("/") }}';
            const url = base + '/live/' + encodeURIComponent(user.username) + '/' + encodeURIComponent(user.password) + '/' + streamId + '.m3u8';
            document.getElementById('video').src = url;
        }

        function playVod(vodId, ext) {
            const base = '{{ url("/") }}';
            const url = base + '/movie/' + encodeURIComponent(user.username) + '/' + encodeURIComponent(user.password) + '/' + vodId + '.' + ext;
            document.getElementById('video').src = url;
        }
    </script>
</body>
</html>

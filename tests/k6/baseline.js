import http from 'k6/http';
import { check, sleep } from 'k6';

// Phase 1 baseline: public read paths. Run: k6 run tests/k6/baseline.js
// Gate: p95 < 800ms at 50 VUs. Scale VUs to find the ceiling.
export const options = {
  stages: [
    { duration: '20s', target: 10 },
    { duration: '40s', target: 50 },
    { duration: '20s', target: 0 },
  ],
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<800'],
  },
};

const BASE = __ENV.BASE_URL || 'http://localhost:8765';
const API = __ENV.API_URL || 'http://127.0.0.1:8000/api';

export default function () {
  const pages = [
    `${BASE}/`,
    `${BASE}/?city=Dar%20es%20Salaam`,
    `${BASE}/hotel-detail/11`,
    `${BASE}/join-us`,
    `${BASE}/login`,
  ];
  const apis = [
    `${API}/properties?per_page=20`,
    `${API}/properties?city=Dar%20es%20Salaam&per_page=20`,
  ];
  const p = pages[Math.floor(Math.random() * pages.length)];
  const r1 = http.get(p);
  check(r1, { [`page ${r1.status}==200`]: (r) => r.status === 200 });
  const a = apis[Math.floor(Math.random() * apis.length)];
  const r2 = http.get(a, { headers: { Accept: 'application/json' } });
  check(r2, { [`api ${r2.status}==200`]: (r) => r.status === 200 });
  sleep(1);
}

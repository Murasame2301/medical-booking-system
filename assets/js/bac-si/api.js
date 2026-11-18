// Goi API cua khu bac si. Token CSRF do layout/header.php in ra:
//   <meta name="csrf-token" content="...">
// Server doc token tu header X-CSRF-Token (xem app/Helpers/csrf.php).

function layTokenCsrf() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : "";
}

async function request(url, { method = "GET", data = null } = {}) {
  // gui kem moi request (GET, POST JSON, FormData); server chi kiem tra o POST
  const config = {
    method,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-Token": layTokenCsrf(),
    },
    credentials: "same-origin",
  };

  if (data instanceof FormData) {
    config.body = data; // upload file: khong set Content-Type
  } else if (data !== null) {
    config.headers["Content-Type"] = "application/json";
    config.body = JSON.stringify(data);
  }

  let res;
  try {
    res = await fetch(url, config);
  } catch {
    throw new Error("Không kết nối được máy chủ.");
  }

  let json = null;
  try {
    json = await res.json();
  } catch {
    throw new Error(`Phản hồi không hợp lệ (HTTP ${res.status})`);
  }

  // 401: phien het han -> ve trang dang nhap
  if (res.status === 401 && json?.msg) {
    window.location.href = "index.php?page=dang-nhap";
    throw new Error(json.msg);
  }

  if (!res.ok || !json?.ok) {
    throw new Error(json?.msg || `Lỗi HTTP ${res.status}`);
  }
  return json;
}

const get = (url) => request(url);
const post = (url, data) => request(url, { method: "POST", data });

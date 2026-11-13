async function request(url, { method = "GET", data = null } = {}) {
  const config = {
    method,
    headers: { "X-Requested-With": "XMLHttpRequest" },
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

  if (!res.ok || !json?.ok) {
    throw new Error(json?.msg || `Lỗi HTTP ${res.status}`);
  }
  return json;
}

const get = (url) => request(url);
const post = (url, data) => request(url, { method: "POST", data });
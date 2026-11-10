async function request(url, options) {
  options = options || {};
  const method = options.method || "GET";
  const data = options.data ?? null;

  const config = {
    method: method,
    headers: { "X-Requested-With": "XMLHttpRequest" },
  };

  if (data !== null) {
    if (data instanceof FormData) {
      config.body = data; // upload file: khong set Content-Type
    } else {
      config.headers["Content-Type"] = "application/json";
      config.body = JSON.stringify(data);
    }
  }
  const res = await fetch(url, config);
  
  let json;
  try {
    json = await res.json();
  } catch (e) {
    throw new Error("Phản hồi không hợp lệ (HTTP " + res.status + ")");
  }

  if (!res.ok || !json.ok) {
    throw new Error(json.msg || "Lỗi HTTP " + res.status);
  }
  return json;
}

function get(url) {
  return request(url);
}

function post(url, data) {
  return request(url, { method: "POST", data: data });
}

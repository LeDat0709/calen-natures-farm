"""Local preview of calennaturesfarm.com before its domain exists.

Forwards http://localhost:PORT to the Hostinger server with the real Host header
and rewrites absolute links back to localhost.
ponytail: HTTP only, buffers whole responses; delete once the domain resolves.
"""
import http.client
import re
import sys
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

SITE = "calennaturesfarm.com"
ORIGIN_IP = "5.183.10.125"
PORT = int(sys.argv[1]) if len(sys.argv) > 1 else 8790
LOCAL = f"localhost:{PORT}"
HOP_HEADERS = {"connection", "keep-alive", "transfer-encoding", "content-length",
               "content-encoding", "upgrade", "proxy-connection"}
TEXT_TYPES = ("text/", "application/json", "application/javascript", "application/xml")
# Plain, JSON-escaped (\/) and URL-encoded (%3A%2F, %3A%5C%2F) forms of the site URL.
SITE_URL = re.compile(rb"https?(:|%3A)(//|\\/\\/|%2F%2F|%5C%2F%5C%2F)(www\.)?" + re.escape(SITE.encode()), re.I)


def rewrite(data: bytes) -> bytes:
    return SITE_URL.sub(lambda m: b"http" + m.group(1) + m.group(2) + LOCAL.encode(), data)


class Proxy(BaseHTTPRequestHandler):
    def _forward(self):
        length = int(self.headers.get("Content-Length") or 0)
        body = self.rfile.read(length) if length else None
        headers = {k: v for k, v in self.headers.items()
                   if k.lower() not in HOP_HEADERS | {"host", "accept-encoding"}}
        for key in ("Origin", "Referer"):
            if key in headers:
                headers[key] = headers[key].replace(f"http://{LOCAL}", f"http://{SITE}")
        headers["Host"] = SITE
        try:
            conn = http.client.HTTPConnection(ORIGIN_IP, 80, timeout=60)
            conn.request(self.command, self.path, body=body, headers=headers)
            resp = conn.getresponse()
            data = resp.read()
        except OSError as err:
            self.send_error(502, f"Cannot reach Hostinger server: {err}")
            return
        ctype = resp.getheader("Content-Type", "")
        if ctype.startswith(TEXT_TYPES):
            data = rewrite(data)
        self.send_response(resp.status, resp.reason)
        for key, value in resp.getheaders():
            if key.lower() in HOP_HEADERS | {"cache-control", "expires", "etag", "last-modified"}:
                continue
            if key.lower() == "location":
                value = rewrite(value.encode()).decode()
            if key.lower() == "set-cookie":
                value = re.sub(r";\s*domain=[^;]*", "", value, flags=re.I)
                value = re.sub(r";\s*secure", "", value, flags=re.I)
            self.send_header(key, value)
        self.send_header("Cache-Control", "no-store")  # preview must always show the latest edit
        self.send_header("Content-Length", str(len(data)))
        self.end_headers()
        if self.command != "HEAD":
            self.wfile.write(data)

    do_GET = do_POST = do_HEAD = do_PUT = do_DELETE = do_PATCH = do_OPTIONS = _forward


if __name__ == "__main__":
    assert rewrite(b'href="https://calennaturesfarm.com/shop"') == f'href="http://{LOCAL}/shop"'.encode()
    assert rewrite(b'"http:\\/\\/calennaturesfarm.com\\/x"') == f'"http:\\/\\/{LOCAL}\\/x"'.encode()
    assert rewrite(b"http%3A%5C%2F%5C%2Fcalennaturesfarm.com%5C%2F") == f"http%3A%5C%2F%5C%2F{LOCAL}%5C%2F".encode()
    assert rewrite(b"url=http%3A%2F%2Fcalennaturesfarm.com%2Fcart") == f"url=http%3A%2F%2F{LOCAL}%2Fcart".encode()
    print(f"Preview: http://{LOCAL}  ->  {SITE} @ {ORIGIN_IP}", flush=True)
    ThreadingHTTPServer(("127.0.0.1", PORT), Proxy).serve_forever()

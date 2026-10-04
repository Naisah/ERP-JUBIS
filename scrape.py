import urllib.request
import re
from html.parser import HTMLParser

class Parser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.in_heading = False
        self.headings = []
        self.current = ""

    def handle_starttag(self, tag, attrs):
        if tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']:
            self.in_heading = True

    def handle_endtag(self, tag):
        if tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']:
            self.in_heading = False
            if self.current.strip():
                self.headings.append(self.current.strip())
            self.current = ""

    def handle_data(self, data):
        if self.in_heading:
            self.current += data

req = urllib.request.Request('https://jubismarketing.com/products/', headers={'User-Agent': 'Mozilla/5.0'})
try:
    html = urllib.request.urlopen(req).read().decode('utf-8')
    p = Parser()
    p.feed(html)
    for h in set(p.headings):
        print(h)
except Exception as e:
    print(e)

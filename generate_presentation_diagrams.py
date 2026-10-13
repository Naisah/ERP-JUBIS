import os
from PIL import Image, ImageDraw, ImageFont

output_dir = 'public/images/presentation'
os.makedirs(output_dir, exist_ok=True)

# Helper to find a standard windows font
def get_font(size, bold=False):
    font_names = [
        'segoeuib.ttf' if bold else 'segoeui.ttf',
        'arialbd.ttf' if bold else 'arial.ttf',
        'calibrib.ttf' if bold else 'calibri.ttf'
    ]
    for fn in font_names:
        p = os.path.join(r'C:\Windows\Fonts', fn)
        if os.path.exists(p):
            return ImageFont.truetype(p, size)
    return ImageFont.load_default()

# 1. Diagram: Full End-to-End B2B ERP Workflow
def make_workflow_diagram():
    w, h = 1600, 600
    img = Image.new('RGB', (w, h), (248, 249, 250))
    d = ImageDraw.Draw(img)
    
    title_font = get_font(26, bold=True)
    step_font = get_font(18, bold=True)
    body_font = get_font(14, bold=False)
    role_font = get_font(12, bold=True)
    
    # Title
    d.text((40, 30), "End-to-End Enterprise Workflow: RFQ to Fulfillment", fill=(10, 25, 49), font=title_font)
    
    steps = [
        ("1. Browse & RFQ", "Client adds products to RFQ cart and submits bulk quote request.", "CLIENT ROLE", (37, 99, 235)),
        ("2. Review & Price", "Sales department reviews, applies wholesale discounts & approves.", "SALES ROLE", (217, 4, 41)),
        ("3. Invoice & Pay", "Invoice auto-created. Pay via PayMongo (GCash/Cards) or Net-30.", "FINANCE ROLE", (245, 158, 11)),
        ("4. Pick & Pack", "Warehouse allocates stock, generates packing list & movement log.", "WAREHOUSE ROLE", (16, 185, 129)),
        ("5. Dispatch & Track", "Shipment created with tracking #. Live client tracking simulator.", "LOGISTICS ROLE", (109, 40, 217))
    ]
    
    card_w = 270
    card_h = 380
    start_x = 40
    y = 100
    gap = 40
    
    for i, (title, desc, role, color) in enumerate(steps):
        x = start_x + i * (card_w + gap)
        # Card Background
        d.rounded_rectangle([x, y, x + card_w, y + card_h], radius=16, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        # Header banner inside card
        d.rounded_rectangle([x, y, x + card_w, y + 60], radius=16, fill=color)
        d.rectangle([x, y + 40, x + card_w, y + 60], fill=color)
        d.text((x + 20, y + 18), title, fill=(255, 255, 255), font=step_font)
        
        # Role badge
        badge_w = 140
        d.rounded_rectangle([x + 20, y + 80, x + 20 + badge_w, y + 106], radius=8, fill=(241, 245, 249))
        d.text((x + 30, y + 86), role, fill=color, font=role_font)
        
        # Description
        words = desc.split()
        lines = []
        cur = ""
        for w_word in words:
            if len(cur + " " + w_word) < 28:
                cur += (" " if cur else "") + w_word
            else:
                lines.append(cur)
                cur = w_word
        if cur: lines.append(cur)
        
        dy = y + 130
        for line in lines:
            d.text((x + 20, dy), line, fill=(71, 85, 105), font=body_font)
            dy += 24
            
        # Draw arrow to next step
        if i < len(steps) - 1:
            arrow_x = x + card_w + 10
            arrow_y = y + card_h // 2
            d.polygon([(arrow_x, arrow_y - 12), (arrow_x + 18, arrow_y), (arrow_x, arrow_y + 12)], fill=(148, 163, 184))
            d.rectangle([arrow_x - 10, arrow_y - 4, arrow_x, arrow_y + 4], fill=(148, 163, 184))
            
    img.save(os.path.join(output_dir, 'diagram_workflow.png'), quality=95)
    print("Created diagram_workflow.png")

# 2. Diagram: Multi-Department Role Architecture
def make_rbac_diagram():
    w, h = 1600, 600
    img = Image.new('RGB', (w, h), (248, 249, 250))
    d = ImageDraw.Draw(img)
    
    title_font = get_font(26, bold=True)
    card_title_font = get_font(20, bold=True)
    item_font = get_font(14, bold=False)
    
    d.text((40, 30), "Enterprise Role-Based Access Control (RBAC) Hierarchy", fill=(10, 25, 49), font=title_font)
    
    roles = [
        ("Super Admin / CEO", (10, 25, 49), [
            "• Full system governance & settings",
            "• User administration & role assignment",
            "• Financial reporting & high-level audit",
            "• All department overrides"
        ]),
        ("Sales Department", (217, 4, 41), [
            "• Quote review workbench",
            "• Price adjustments & approvals",
            "• Client account management",
            "• Pipeline monitoring"
        ]),
        ("Finance Department", (245, 158, 11), [
            "• Invoices & payment verification",
            "• Manual payment logging",
            "• Accounts receivable tracking",
            "• VAT & tax report generation"
        ]),
        ("Purchasing & Sourcing", (37, 99, 235), [
            "• Supplier directory maintenance",
            "• Purchase Order (PO) creation",
            "• Procurement cost tracking",
            "• Reorder threshold monitoring"
        ]),
        ("Warehouse & Logistics", (16, 185, 129), [
            "• Inventory counts & adjustments",
            "• Stock movement audit trail",
            "• Shipment dispatch & carrier assignment",
            "• Delivery status synchronization"
        ]),
        ("B2B Client Account", (100, 116, 139), [
            "• Catalog & specification browsing",
            "• RFQ Cart submission",
            "• Order history & status tracking",
            "• PayMongo checkout & Net-30 credit"
        ])
    ]
    
    # 2 rows of 3 columns
    card_w = 480
    card_h = 210
    start_x = 40
    start_y = 90
    gap_x = 40
    gap_y = 30
    
    for i, (role_name, col, items) in enumerate(roles):
        row = i // 3
        col_idx = i % 3
        x = start_x + col_idx * (card_w + gap_x)
        y = start_y + row * (card_h + gap_y)
        
        d.rounded_rectangle([x, y, x + card_w, y + card_h], radius=14, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        d.rounded_rectangle([x, y, x + 8, y + card_h], radius=4, fill=col)
        
        d.text((x + 24, y + 18), role_name, fill=col, font=card_title_font)
        
        item_y = y + 60
        for item in items:
            d.text((x + 24, item_y), item, fill=(51, 65, 85), font=item_font)
            item_y += 32
            
    img.save(os.path.join(output_dir, 'diagram_rbac.png'), quality=95)
    print("Created diagram_rbac.png")

# 3. Diagram: Full Stack Architecture & Technology Stack
def make_tech_stack_diagram():
    w, h = 1600, 600
    img = Image.new('RGB', (w, h), (248, 249, 250))
    d = ImageDraw.Draw(img)
    
    title_font = get_font(26, bold=True)
    card_title_font = get_font(20, bold=True)
    item_font = get_font(14, bold=False)
    
    d.text((40, 30), "Full-Stack Enterprise Technology Architecture", fill=(10, 25, 49), font=title_font)
    
    layers = [
        ("Presentation Layer", (217, 4, 41), [
            "• Vue 3 Composition API & Single Page App",
            "• Inertia.js (Seamless Server-Driven SPA)",
            "• Tailwind CSS Enterprise Design System",
            "• Heroicons & Chart.js Data Visualizations",
            "• Responsive Mobile & Desktop Layouts"
        ]),
        ("Application & Business Logic", (10, 25, 49), [
            "• Laravel 12 Enterprise Framework (PHP 8.2+)",
            "• Role-Based Access Control (RBAC) Middleware",
            "• Database Transactions & Idempotency Guards",
            "• Automated Mails & Background Notifications",
            "• DomPDF Automated Invoice PDF Generation"
        ]),
        ("Database & Persistence Layer", (37, 99, 235), [
            "• MySQL 8.4 Relational Database Management",
            "• Foreign Key Cascades & Strict Integrity Constraints",
            "• Stock Movements Immutable Audit Ledger",
            "• Scalable Normalized Relational Schema",
            "• Database-Backed Session & Queue Management"
        ]),
        ("Cloud Infrastructure & Security", (16, 185, 129), [
            "• Laravel Cloud Hosted Infrastructure",
            "• Asia Pacific (Singapore) Edge Deployment",
            "• Scale-to-Zero Compute & Database Architecture",
            "• PayMongo Gateway (Cards, GCash, Maya, QR Ph)",
            "• HMAC-SHA256 Webhook Replay-Resistant Security"
        ])
    ]
    
    card_w = 360
    card_h = 440
    start_x = 40
    y = 90
    gap = 25
    
    for i, (layer_name, col, items) in enumerate(layers):
        x = start_x + i * (card_w + gap)
        d.rounded_rectangle([x, y, x + card_w, y + card_h], radius=14, fill=(255, 255, 255), outline=(226, 232, 240), width=2)
        d.rounded_rectangle([x, y, x + card_w, y + 55], radius=14, fill=col)
        d.rectangle([x, y + 35, x + card_w, y + 55], fill=col)
        
        d.text((x + 20, y + 15), layer_name, fill=(255, 255, 255), font=card_title_font)
        
        item_y = y + 80
        for item in items:
            d.text((x + 20, item_y), item, fill=(51, 65, 85), font=item_font)
            item_y += 38
            
    img.save(os.path.join(output_dir, 'diagram_tech_stack.png'), quality=95)
    print("Created diagram_tech_stack.png")

make_workflow_diagram()
make_rbac_diagram()
make_tech_stack_diagram()

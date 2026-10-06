<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Jubis Marketing (Simulation)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            background-color: #f9fafb; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .hidden { display: none !important; }
        .gcash-bg { background: linear-gradient(135deg, #007CFF 0%, #005ce6 100%); }
        .card-bg { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center py-10">

    <!-- Jubis Marketing Header -->
    <div class="mb-6 text-center">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center justify-center">
            <svg class="w-8 h-8 mr-3 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Jubis Marketing
        </h1>
        <p class="text-slate-500 text-sm mt-1 font-medium uppercase tracking-widest">Secure Checkout Portal</p>
    </div>

    <div class="max-w-md w-full glass-panel rounded-2xl shadow-2xl overflow-hidden border border-gray-100 relative transition-all duration-300">
        
        <!-- Main Form Wrapper -->
        <form id="main-form" action="{{ url('/api/mock/payment/process/invoice/' . $invoice->id) }}" method="POST">
            @csrf
            <input type="hidden" name="payment_method" id="final_method_input" value="">
            
            <!-- Default Header for Selection & Card -->
            <div id="standard-header" class="bg-slate-900 px-6 py-5 text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 1px, transparent 0, transparent 50%); background-size: 10px 10px;"></div>
                <h2 class="text-white text-lg font-bold tracking-wider relative z-10">ORDER SUMMARY</h2>
                <div class="text-slate-300 text-xs mt-1 relative z-10 flex justify-center space-x-2">
                    <span>Invoice #INV-{{ str_pad($invoice->quote_id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>

            <!-- Amount Display -->
            <div id="amount-display" class="p-6 text-center bg-white border-b border-gray-100">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Due</p>
                <h1 class="text-4xl font-extrabold text-slate-900">₱{{ number_format($invoice->total_amount, 2) }}</h1>
            </div>

            <div class="p-6 bg-white">
                
                <!-- VIEW 1: SELECTION -->
                <div id="view-selection">
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Select Payment Method</h3>
                    <div class="space-y-3 mb-6">
                        
                        <!-- Credit Card Option -->
                        <label class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-200" onclick="selectMethod('card', this)">
                            <input type="radio" name="method_select" value="card" class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="ml-3 font-bold text-slate-700 flex-1">Credit / Debit Card</span>
                            <div class="flex space-x-1">
                                <!-- Visa SVG -->
                                <svg class="h-6 w-auto" viewBox="0 0 38 24" fill="none"><path d="M38 0H0V24H38V0Z" fill="#1434CB"/><path d="M26.2415 7.15112L24.4754 18.0004H21.5647L23.3283 7.15112H26.2415ZM17.1584 7.15112L14.6293 14.7709L14.0048 11.5937C13.6844 10.1583 12.5694 8.79051 10.7412 8.0315L11.3657 11.1963C11.3657 11.1963 12.8711 11.5937 13.5684 12.5186L15.3406 18.0004H18.397L21.5165 7.15112H18.6653C18.2323 7.15112 17.8437 7.4116 17.7027 7.82859L17.1584 7.15112ZM32.3276 18.0004L34.1953 7.15112H31.5434C31.0655 7.15112 30.655 7.4646 30.4908 7.91524L26.177 18.0004H29.2311L29.8407 16.2739H33.567L33.9175 18.0004H32.3276ZM30.6698 13.9197L31.8122 10.6033L32.6133 13.9197H30.6698ZM10.5986 7.15112H3.77148L3.63053 7.81896C5.54446 8.32014 7.61868 9.30081 8.87525 10.4285L7.61868 18.0004H10.6409L10.5986 7.15112Z" fill="white"/></svg>
                                <!-- Mastercard SVG -->
                                <svg class="h-6 w-auto" viewBox="0 0 38 24" fill="none"><rect width="38" height="24" rx="3" fill="#252525"/><circle cx="15" cy="12" r="7" fill="#EB001B"/><circle cx="23" cy="12" r="7" fill="#F79E1B"/><path fill-rule="evenodd" clip-rule="evenodd" d="M19 17.656C20.3707 16.4173 21.2 14.331 21.2 12C21.2 9.669 20.3707 7.5827 19 6.344C17.6293 7.5827 16.8 9.669 16.8 12C16.8 14.331 17.6293 16.4173 19 17.656Z" fill="#FF5F00"/></svg>
                            </div>
                        </label>

                        <!-- GCash Option -->
                        <label class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-200" onclick="selectMethod('gcash', this)">
                            <input type="radio" name="method_select" value="gcash" class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="ml-3 font-bold text-slate-700 flex-1">GCash</span>
                            <div class="bg-blue-600 text-white text-[10px] font-black px-2 py-1 rounded tracking-wider italic">GCash</div>
                        </label>

                        <!-- QR Ph Option -->
                        <label class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-200" onclick="selectMethod('qrph', this)">
                            <input type="radio" name="method_select" value="qrph" class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="ml-3 font-bold text-slate-700 flex-1">QR Ph (Live API)</span>
                            <div class="bg-slate-900 text-white text-[10px] font-bold px-2 py-1 rounded flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h4v4H4V4zm6 0h10v2H10V4zm0 4h10v2H10V8zm0 4h10v2H10v-2zM4 10h4v4H4v-4zm0 6h4v4H4v-4zm6 0h10v2H10v-2zm0 4h10v2H10v-2z"/></svg> QR Ph
                            </div>
                        </label>
                    </div>
                    
                    <button type="button" onclick="proceedToPayment()" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 rounded-xl shadow-lg transition-colors flex items-center justify-center text-sm tracking-wide">
                        CONTINUE TO SECURE PAYMENT <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

                <!-- VIEW 2: CREDIT CARD MOCKUP -->
                <div id="view-card" class="hidden animate-fade-in">
                    <button type="button" onclick="goBack()" class="text-sm text-blue-600 font-bold mb-6 flex items-center hover:text-blue-800">&larr; Change Payment Method</button>
                    
                    <!-- Visual Card Graphic -->
                    <div class="card-bg text-white p-5 rounded-xl shadow-lg mb-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10"></div>
                        <div class="flex justify-between items-center mb-6 relative z-10">
                            <svg class="w-8 h-8 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M2 5a2 2 0 012-2h16a2 2 0 012 2v14a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 0v2h16V5H4zm16 6H4v8h16v-8z"/></svg>
                            <div class="font-bold tracking-widest text-sm opacity-80">TEST CARD</div>
                        </div>
                        <div class="font-mono text-xl tracking-[0.2em] mb-2 relative z-10">4242 4242 4242 4242</div>
                        <div class="flex justify-between text-xs font-mono opacity-80 relative z-10">
                            <span>JOHN DOE</span>
                            <span>12/28</span>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Card Number</label>
                            <input type="text" placeholder="4242 4242 4242 4242" class="w-full border-slate-300 rounded-lg shadow-sm px-4 py-3 border focus:ring-blue-500 focus:border-blue-500 font-mono bg-slate-50" required>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Expiry</label>
                                <input type="text" placeholder="MM/YY" class="w-full border-slate-300 rounded-lg shadow-sm px-4 py-3 border focus:ring-blue-500 focus:border-blue-500 font-mono bg-slate-50" required>
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">CVC</label>
                                <input type="password" placeholder="***" class="w-full border-slate-300 rounded-lg shadow-sm px-4 py-3 border focus:ring-blue-500 focus:border-blue-500 font-mono bg-slate-50" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-transform transform hover:scale-[1.02] flex justify-between px-6 items-center">
                        <span>Pay Securely</span>
                        <span class="text-lg">₱{{ number_format($invoice->total_amount, 2) }}</span>
                    </button>
                </div>
            </div>

            <!-- VIEW 3: GCASH MOCKUP -->
            <div id="view-gcash" class="hidden animate-fade-in w-full h-[600px] relative bg-[#f3f4f6] flex flex-col rounded-b-2xl overflow-hidden">
                <!-- Top Blue Background -->
                <div class="absolute top-0 left-0 w-full h-40 bg-[#005ce6] z-0"></div>
                
                <!-- Navbar -->
                <div class="relative z-10 p-4 flex items-center justify-between">
                    <button type="button" onclick="goBack()" class="text-white text-sm font-bold flex items-center hover:opacity-80">
                        &larr; Back
                    </button>
                    <div class="flex items-center text-white font-bold text-xl tracking-wide mr-6">
                        <!-- GCash Logo approximation -->
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2-5.5h4V13h-4v1.5zm0-3h4V10h-4v1.5z"/></svg>
                        GCash
                    </div>
                    <div class="w-12"></div> <!-- Spacer -->
                </div>
                
                <!-- Main White Card -->
                <div class="relative z-10 flex-1 px-4 pb-6 mt-2 flex flex-col">
                    <div class="bg-white rounded-xl shadow-md p-6 flex-1 flex flex-col">
                        
                        <!-- Merchant Name -->
                        <h2 class="text-center text-[#005ce6] font-bold text-lg mb-8">Jubis Marketing</h2>
                        
                        <!-- PAY WITH -->
                        <div class="mb-8">
                            <h3 class="text-gray-400 text-xs font-bold uppercase mb-4 tracking-wider">Pay With</h3>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 font-medium text-sm">GCash</span>
                                <div class="flex items-center">
                                    <div class="text-right mr-3">
                                        <p class="text-gray-700 font-medium text-sm">PHP {{ number_format($invoice->total_amount + 5000, 2) }}</p>
                                        <p class="text-gray-400 text-xs">Available Balance</p>
                                    </div>
                                    <!-- Custom Radio Button (Selected) -->
                                    <div class="w-5 h-5 rounded-full border-2 border-[#005ce6] flex items-center justify-center">
                                        <div class="w-2.5 h-2.5 bg-[#005ce6] rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- YOU ARE ABOUT TO PAY -->
                        <div class="mb-8">
                            <h3 class="text-gray-400 text-xs font-bold uppercase mb-4 tracking-wider">You are about to pay</h3>
                            <div class="flex justify-between mb-4">
                                <span class="text-gray-400 text-sm font-medium">Amount</span>
                                <span class="text-gray-600 text-sm font-medium">PHP {{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-100 pt-5 mt-2">
                                <span class="text-gray-600 font-bold text-sm">Total</span>
                                <span class="text-black font-extrabold text-xl">PHP {{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                        </div>
                        
                        <!-- Footer / Button -->
                        <div class="mt-auto">
                            <p class="text-center text-gray-400 text-[11px] px-6 mb-5 leading-relaxed">
                                Please review to ensure that the details are correct before you proceed.
                            </p>
                            
                            <button type="submit" class="w-full bg-[#005ce6] hover:bg-blue-700 text-white font-bold py-3.5 rounded-full shadow-sm transition-colors text-sm uppercase tracking-wide">
                                Pay PHP {{ number_format($invoice->total_amount, 2) }}
                            </button>
                        </div>
                        
                    </div>
                </div>
            </div>

            <!-- END OF VIEWS -->
            
        </form>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-slate-400 text-xs flex flex-col items-center">
        <p class="mb-2">&copy; {{ date('Y') }} Jubis Marketing. All rights reserved.</p>
        <p class="flex items-center bg-slate-200 px-3 py-1 rounded-full text-slate-500 font-medium">
            <svg class="w-3 h-3 mr-1 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
            Secured by PayMongo API Sandbox
        </p>
    </div>

    <script>
        let selectedMethod = null;

        function selectMethod(method, element) {
            selectedMethod = method;
            
            // Remove active classes from all labels
            document.querySelectorAll('label').forEach(el => {
                el.classList.remove('border-blue-500', 'bg-blue-50');
                el.classList.add('border-slate-200');
            });
            
            // Add active class to clicked
            element.classList.remove('border-slate-200');
            element.classList.add('border-blue-500', 'bg-blue-50');
            
            // Check the radio
            element.querySelector('input').checked = true;
        }

        function proceedToPayment() {
            if (!selectedMethod) {
                alert('Please select a payment method first.');
                return;
            }

            document.getElementById('final_method_input').value = selectedMethod;

            if (selectedMethod === 'qrph') {
                // Submit directly for QR Ph (redirects to live API)
                document.getElementById('main-form').submit();
                return;
            }

            // Hide Selection
            document.getElementById('view-selection').classList.add('hidden');
            
            if (selectedMethod === 'card') {
                document.getElementById('view-card').classList.remove('hidden');
            } else if (selectedMethod === 'gcash') {
                // Hide standard headers for GCash full screen takeover
                document.getElementById('standard-header').classList.add('hidden');
                document.getElementById('amount-display').classList.add('hidden');
                document.getElementById('view-gcash').classList.remove('hidden');
            }
        }

        function goBack() {
            document.getElementById('view-card').classList.add('hidden');
            document.getElementById('view-gcash').classList.add('hidden');
            
            document.getElementById('standard-header').classList.remove('hidden');
            document.getElementById('amount-display').classList.remove('hidden');
            document.getElementById('view-selection').classList.remove('hidden');
        }
    </script>
</body>
</html>

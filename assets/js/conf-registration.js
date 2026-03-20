(function() {
	if (!window.confAppConfig) return;

	class ConfRegistrationApp {
		constructor(config) {
			this.config = config;
			this.mountPoint = document.getElementById('conf-app-mount');
			this.state = {
				step: 1,
				attendees: [{ name: '', email: '', phone: '', company: '', jobTitle: '' }],
				ticketId: null,
				paymentMethod: 'wechat',
				orderData: null,
				ticketsData: { tickets: [], discount: { enabled: false, threshold: 3, percentage: 15 } }
			};
			this.init();
		}

		async init() {
			try {
				const res = await fetch(`${this.config.apiUrl}/tickets`);
				if (!res.ok) throw new Error('Failed to load configuration');
				this.state.ticketsData = await res.json();
				
				// set default ticket id
				if (this.state.ticketsData.tickets.length > 0) {
					this.state.ticketId = this.state.ticketsData.tickets[0].id;
				}

				this.render();
			} catch (error) {
				console.error(error);
				this.mountPoint.innerHTML = `<div class="p-8 text-center text-red-600 flex flex-col items-center justify-center min-h-screen"><span class="material-symbols-outlined text-4xl mb-4">error</span><p>System currently unavailable. Please try again later.</p></div>`;
			}
		}

		updateAttendee(index, field, value) {
			this.state.attendees[index][field] = value;
		}

		addAttendee() {
			this.state.attendees.push({ name: '', email: '', phone: '', company: '', jobTitle: '' });
			this.render();
		}

		removeAttendee(index) {
			if (this.state.attendees.length > 1) {
				this.state.attendees.splice(index, 1);
				this.render();
			}
		}

		validateStep1() {
			for (let i = 0; i < this.state.attendees.length; i++) {
				const a = this.state.attendees[i];
				if (!a.name || !a.phone || (!a.email && i === 0)) {
					alert(`Please fill in Name and Phone for Attendee ${i + 1}`);
					return false;
				}
			}
			return true;
		}

		nextStep() {
			if (this.state.step === 1 && !this.validateStep1()) return;
			this.state.step++;
			window.scrollTo(0, 0);
			this.render();
		}

		prevStep() {
			if (this.state.step > 1) {
				this.state.step--;
				window.scrollTo(0, 0);
				this.render();
			}
		}

		render() {
			let content = '';
			switch (this.state.step) {
				case 1: content = this.getStep1HTML(); break;
				case 2: content = this.getStep2HTML(); break;
				case 3: content = this.getStep3HTML(); break;
				case 4: content = this.getStep4HTML(); break;
			}
			this.mountPoint.innerHTML = content;
			this.bindEvents();
		}

		getStep1HTML() {
			let attendeesHTML = this.state.attendees.map((a, i) => `
				<div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-6 transition-all">
					<div class="flex justify-between items-center mb-6">
						<div class="flex items-center gap-3">
							<div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-sm">${i + 1}</div>
							<h3 class="text-lg font-bold tracking-tight text-gray-900">${i === 0 ? 'Primary Attendee 联系人' : 'Additional Attendee 附加参会人'}</h3>
						</div>
						${i > 0 ? `<button class="text-red-500 hover:text-red-700 font-bold text-sm action-remove-attendee" data-index="${i}">Remove</button>` : ''}
					</div>
					<div class="space-y-6">
						<div class="relative">
							<label class="block text-xs font-medium tracking-wider text-gray-500 uppercase mb-1">Full Name 姓名 *</label>
							<input class="input-field w-full bg-transparent border-0 border-b border-gray-200 py-3 focus:ring-0 focus:border-blue-500 transition-colors text-lg" 
								placeholder="e.g. Alex Rivera" type="text" data-index="${i}" data-field="name" value="${a.name}" />
						</div>
						<div class="relative">
							<label class="block text-xs font-medium tracking-wider text-gray-500 uppercase mb-1">Phone Number 手机号 *</label>
							<input class="input-field w-full bg-transparent border-0 border-b border-gray-200 py-3 focus:ring-0 focus:border-blue-500 transition-colors text-lg" 
								placeholder="13800000000" type="tel" data-index="${i}" data-field="phone" value="${a.phone}" />
						</div>
						<div class="relative">
							<label class="block text-xs font-medium tracking-wider text-gray-500 uppercase mb-1">Email Address 邮箱</label>
							<input class="input-field w-full bg-transparent border-0 border-b border-gray-200 py-3 focus:ring-0 focus:border-blue-500 transition-colors text-lg" 
								placeholder="alex@example.com" type="email" data-index="${i}" data-field="email" value="${a.email}" />
						</div>
						<div class="relative">
							<label class="block text-xs font-medium tracking-wider text-gray-500 uppercase mb-1">Organization 组织机构</label>
							<input class="input-field w-full bg-transparent border-0 border-b border-gray-200 py-3 focus:ring-0 focus:border-blue-500 transition-colors text-lg" 
								placeholder="Company / University" type="text" data-index="${i}" data-field="company" value="${a.company}" />
						</div>
					</div>
				</div>
			`).join('');

			let discountMsg = '';
			if (this.state.ticketsData.discount.enabled) {
				discountMsg = `
					<div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-100 flex gap-4">
						<span class="material-symbols-outlined text-blue-600">info</span>
						<div>
							<h4 class="font-bold text-blue-800 mb-1">Group Discount Available 团体优惠</h4>
							<p class="text-sm text-blue-600/80 leading-relaxed">Registering ${this.state.ticketsData.discount.threshold} or more attendees applies a ${this.state.ticketsData.discount.percentage}% discount.</p>
						</div>
					</div>
				`;
			}

			return `
				<header class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100 flex items-center justify-between px-6 py-4">
					<div class="flex items-center gap-4">
						<h1 class="font-bold tracking-tight text-gray-900 text-lg">Event Registration</h1>
					</div>
				</header>
				<main class="pt-24 px-6 max-w-2xl mx-auto pb-32">
					<div class="mb-10">
						<div class="flex justify-between items-end mb-2">
							<p class="text-xs font-medium tracking-wider text-gray-500 uppercase">Step 01 of 03</p>
							<p class="text-xs font-bold text-blue-600">Attendee Details</p>
						</div>
						<div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
							<div class="h-full bg-blue-600 w-1/3 transition-all"></div>
						</div>
					</div>
					<section class="space-y-6">
						<h2 class="text-3xl font-bold tracking-tight text-gray-900 mb-8">Who is joining us? <br><span class="text-xl font-normal text-gray-500">填写参会人员信息</span></h2>
						<div id="attendees-container">
							${attendeesHTML}
						</div>
						<button id="btn-add-attendee" class="w-full py-6 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center gap-3 hover:border-blue-400 hover:bg-blue-50 transition-all text-gray-500 hover:text-blue-600">
							<div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center"><span class="material-symbols-outlined">add</span></div>
							<span class="text-xs font-bold tracking-wider uppercase">Add Another Attendee 添加参会人</span>
						</button>
						${discountMsg}
					</section>
				</main>
				<div class="fixed bottom-0 w-full z-50 bg-white/90 backdrop-blur-md shadow-[0_-12px_32px_rgba(0,0,0,0.05)] px-6 py-6" style="padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
					<div class="max-w-2xl mx-auto flex gap-4">
						<button id="btn-next-step" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all text-center">Continue to Pricing 继续选择票价</button>
					</div>
				</div>
			`;
		}

		getStep2HTML() {
			let ticketsHTML = this.state.ticketsData.tickets.map((t, index) => {
				const isChecked = this.state.ticketId === t.id ? 'checked' : '';
				return `
					<label class="relative cursor-pointer group block mb-6">
						<input ${isChecked} class="sr-only peer ticket-radio" name="ticket_tier" type="radio" value="${t.id}" />
						<div class="p-8 rounded-xl bg-white transition-all duration-300 ring-1 ring-inset ring-gray-200 peer-checked:ring-2 peer-checked:ring-blue-600 peer-checked:bg-blue-50 shadow-[0_4px_12px_rgba(0,0,0,0.02)] hover:shadow-md">
							<div class="flex justify-between items-start mb-6">
								<div>
									<h3 class="text-xl font-bold text-gray-900">${t.name}</h3>
									${t.desc ? `<p class="text-sm text-gray-500 mt-2">${t.desc}</p>` : ''}
								</div>
								<div class="text-right">
									<span class="text-xs text-gray-500 block uppercase tracking-wider mb-1">Price</span>
									<span class="text-2xl font-black text-blue-600">${this.config.currency}${t.price}</span>
								</div>
							</div>
							<div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
								<span class="material-symbols-outlined text-blue-600">verified</span>
							</div>
						</div>
					</label>
				`;
			}).join('');

			if (this.state.ticketsData.tickets.length === 0) {
				ticketsHTML = `<div class="p-8 text-center text-gray-500 bg-white rounded-xl">No tickets available at the moment.</div>`;
			}

			return `
				<header class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100 flex items-center justify-between px-6 py-4">
					<div class="flex items-center gap-4">
						<button class="action-prev active:scale-95 duration-200 text-blue-600 flex items-center justify-center">
							<span class="material-symbols-outlined">arrow_back</span>
						</button>
						<h1 class="font-bold tracking-tight text-gray-900 text-lg">Event Registration</h1>
					</div>
				</header>
				<main class="pt-24 px-6 max-w-2xl mx-auto pb-32">
					<div class="mb-10">
						<div class="flex justify-between items-end mb-2">
							<p class="text-xs font-medium tracking-wider text-gray-500 uppercase">Step 02 of 03</p>
							<p class="text-xs font-bold text-blue-600">Select Ticket</p>
						</div>
						<div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
							<div class="h-full bg-blue-600 w-2/3 transition-all"></div>
						</div>
					</div>
					<div class="mb-10">
						<h2 class="text-3xl font-extrabold tracking-tight leading-tight text-gray-900 mb-2">选择票价类别</h2>
						<p class="text-gray-500 mb-8">Choose the ticket that best fits your needs.</p>
					</div>
					<div class="grid grid-cols-1">
						${ticketsHTML}
					</div>
				</main>
				<div class="fixed bottom-0 w-full z-50 bg-white/90 backdrop-blur-md shadow-[0_-12px_32px_rgba(0,0,0,0.05)] px-6 py-6" style="padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
					<div class="max-w-2xl mx-auto">
						<button class="action-next w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all flex items-center justify-center gap-3">
							Continue to Review 继续确认订单
							<span class="material-symbols-outlined">arrow_forward</span>
						</button>
					</div>
				</div>
			`;
		}

		getStep3HTML() {
			const totals = this.calculateTotals();
			const primary = this.state.attendees[0];

			const wechatChecked = this.state.paymentMethod === 'wechat' ? 'ring-2 ring-blue-600 bg-blue-50' : 'ring-1 ring-gray-200 bg-white';
			const bankChecked = this.state.paymentMethod === 'bank' ? 'ring-2 ring-blue-600 bg-blue-50' : 'ring-1 ring-gray-200 bg-white';
			const onsiteChecked = this.state.paymentMethod === 'onsite' ? 'ring-2 ring-blue-600 bg-blue-50' : 'ring-1 ring-gray-200 bg-white';

			return `
				<header class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100 flex items-center justify-between px-6 py-4">
					<div class="flex items-center gap-4">
						<button class="action-prev active:scale-95 duration-200 text-blue-600 flex items-center justify-center">
							<span class="material-symbols-outlined">arrow_back</span>
						</button>
						<h1 class="font-bold tracking-tight text-gray-900 text-lg">Order Preview 订单预览确认</h1>
					</div>
				</header>
				<main class="pt-24 px-6 max-w-2xl mx-auto pb-32 space-y-8">
					<!-- Progress -->
					<div class="space-y-3 mb-8">
						<div class="flex justify-between items-end">
							<span class="text-xs font-medium uppercase text-gray-500 tracking-wider">Step 03 of 03</span>
							<span class="text-sm font-bold text-blue-600">Review & Payment</span>
						</div>
						<div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
							<div class="h-full w-full bg-blue-600 rounded-full transition-all"></div>
						</div>
					</div>

					<!-- Personal Info -->
					<section class="bg-gray-50 rounded-xl p-6 border border-gray-100">
						<h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
							<span class="material-symbols-outlined text-blue-600">person</span> 个人信息 Personal Info
						</h2>
						<div class="space-y-4">
							<div>
								<p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Full Name</p>
								<p class="font-semibold text-gray-900">${primary.name || '-'}</p>
							</div>
							<div class="grid grid-cols-2 gap-4">
								<div>
									<p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Organization</p>
									<p class="font-semibold text-gray-900">${primary.company || '-'}</p>
								</div>
								<div>
									<p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Phone</p>
									<p class="font-semibold text-gray-900">${primary.phone || '-'}</p>
								</div>
							</div>
						</div>
					</section>

					<!-- Ticket Summary -->
					<section class="bg-white rounded-xl overflow-hidden border border-gray-100 shadow-sm">
						<div class="p-6">
							<h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
								<span class="material-symbols-outlined text-blue-600">confirmation_number</span> 票务摘要 Ticket Summary
							</h2>
							<div class="bg-gray-50 p-4 rounded-lg flex justify-between items-center mb-4">
								<div>
									<h3 class="font-bold text-gray-900">${totals.ticketName}</h3>
									<p class="text-sm text-gray-500">${totals.count} Attendee(s)</p>
								</div>
								<div class="text-right">
									<p class="font-bold text-gray-900">${this.config.currency}${totals.subtotal.toFixed(2)}</p>
								</div>
							</div>
							${totals.discount > 0 ? `
							<div class="flex justify-between text-sm text-green-600 font-medium px-2 py-2">
								<span>团体优惠 Group Discount</span>
								<span>-${this.config.currency}${totals.discount.toFixed(2)}</span>
							</div>` : ''}
						</div>
						<div class="bg-blue-50 p-6 flex justify-between items-center border-t border-blue-100">
							<span class="font-bold text-blue-900">Total Amount 总额</span>
							<span class="text-2xl font-black text-blue-700">${this.config.currency}${totals.total.toFixed(2)}</span>
						</div>
					</section>

					<!-- Payment Options -->
					<section>
						<h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
							<span class="material-symbols-outlined text-blue-600">payments</span> 支付方式 Payment Method
						</h2>
						<div class="space-y-3">
							<label class="block relative cursor-pointer group">
								<input type="radio" name="payment_method" value="wechat" class="sr-only payment-radio" ${this.state.paymentMethod === 'wechat' ? 'checked' : ''}>
								<div class="p-4 rounded-xl transition-all ${wechatChecked}">
									<div class="flex justify-between items-center">
										<div class="flex items-center gap-3">
											<span class="material-symbols-outlined text-green-500 text-3xl">qr_code_2</span>
											<div>
												<h3 class="font-bold text-gray-900">WeChat Pay (微信支付)</h3>
												<p class="text-xs text-gray-500">Instant confirmation 即时确认</p>
											</div>
										</div>
										<div class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center ${this.state.paymentMethod === 'wechat' ? 'bg-blue-600 border-blue-600' : ''}">
											${this.state.paymentMethod === 'wechat' ? '<span class="material-symbols-outlined text-white text-xs">check</span>' : ''}
										</div>
									</div>
								</div>
							</label>

							<label class="block relative cursor-pointer group">
								<input type="radio" name="payment_method" value="bank" class="sr-only payment-radio" ${this.state.paymentMethod === 'bank' ? 'checked' : ''}>
								<div class="p-4 rounded-xl transition-all ${bankChecked}">
									<div class="flex justify-between items-center">
										<div class="flex items-center gap-3">
											<span class="material-symbols-outlined text-blue-500 text-3xl">account_balance</span>
											<div>
												<h3 class="font-bold text-gray-900">Bank Transfer (银行汇款)</h3>
												<p class="text-xs text-gray-500">Requires receipt upload 需上传凭证</p>
											</div>
										</div>
										<div class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center ${this.state.paymentMethod === 'bank' ? 'bg-blue-600 border-blue-600' : ''}">
											${this.state.paymentMethod === 'bank' ? '<span class="material-symbols-outlined text-white text-xs">check</span>' : ''}
										</div>
									</div>
								</div>
							</label>

							<label class="block relative cursor-pointer group">
								<input type="radio" name="payment_method" value="onsite" class="sr-only payment-radio" ${this.state.paymentMethod === 'onsite' ? 'checked' : ''}>
								<div class="p-4 rounded-xl transition-all ${onsiteChecked}">
									<div class="flex justify-between items-center">
										<div class="flex items-center gap-3">
											<span class="material-symbols-outlined text-orange-500 text-3xl">payments</span>
											<div>
												<h3 class="font-bold text-gray-900">On-site (现场缴费)</h3>
												<p class="text-xs text-gray-500">Pay at the venue 现场支付</p>
											</div>
										</div>
										<div class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center ${this.state.paymentMethod === 'onsite' ? 'bg-blue-600 border-blue-600' : ''}">
											${this.state.paymentMethod === 'onsite' ? '<span class="material-symbols-outlined text-white text-xs">check</span>' : ''}
										</div>
									</div>
								</div>
							</label>
						</div>
					</section>
				</main>
				<div class="fixed bottom-0 w-full z-50 bg-white/90 backdrop-blur-md shadow-[0_-12px_32px_rgba(0,0,0,0.05)] px-6 py-6 border-t border-gray-100" style="padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
					<div class="max-w-2xl mx-auto">
						<button id="btn-submit-order" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all flex items-center justify-center gap-3">
							Confirm and Pay 确认并支付
							<span class="material-symbols-outlined">arrow_forward</span>
						</button>
					</div>
				</div>
			`;
		}
		
		calculateTotals() {
			let ticketPrice = 0;
			let ticketName = '';
			const t = this.state.ticketsData.tickets.find(x => x.id === this.state.ticketId);
			if (t) {
				ticketPrice = t.price;
				ticketName = t.name;
			}
			const count = this.state.attendees.length;
			const subtotal = ticketPrice * count;
			let discount = 0;
			const p = this.state.ticketsData.discount;
			if (p.enabled && count >= p.threshold) {
				discount = subtotal * (p.percentage / 100);
			}
			return { ticketName, ticketPrice, count, subtotal, discount, total: subtotal - discount };
		}
		
		getStep4HTML() {
			const isBank = this.state.paymentMethod === 'bank';
			const order = this.state.orderData || { order_id: null, reg_no: 'PENDING', total_amount: 0 };
			const primary = this.state.attendees[0];

			if (isBank && !this.state.receiptUploaded) {
				return `
				<header class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-sm px-6 py-4">
					<h1 class="font-bold tracking-tight text-gray-900 text-lg">Transfer Details 银行汇款详情</h1>
				</header>
				<main class="pt-24 px-6 max-w-2xl mx-auto pb-32">
					<div class="mb-8 text-center">
						<h2 class="text-xs uppercase tracking-wider text-gray-500 mb-2">Amount to Transfer 汇款金额</h2>
						<div class="text-4xl font-extrabold text-blue-600">
							<span class="text-2xl mr-1">${this.config.currency}</span>${order.total_amount}
						</div>
						<p class="text-sm text-gray-500 mt-2">Please transfer exactly this amount.</p>
					</div>
					
					<div class="bg-gray-50 rounded-xl p-6 border border-gray-100 mb-8">
						<h3 class="font-bold text-gray-900 mb-4">Bank Account Info 银行账户信息</h3>
						<div class="space-y-3 text-sm">
							<div><span class="text-gray-500 block text-xs">Account Name 账户名称</span> <span class="font-bold text-gray-900 text-lg">Official Conference Account</span></div>
							<div><span class="text-gray-500 block text-xs mt-3">Bank 开户银行</span> <span class="font-medium text-gray-900">Example Bank</span></div>
							<div><span class="text-gray-500 block text-xs mt-3">Account No. 账号</span> <span class="font-mono font-bold text-gray-900 text-lg tracking-wider">1234-5678-9012</span></div>
							<div class="mt-4 p-4 bg-blue-50 text-blue-800 rounded-lg text-sm flex items-start gap-2 border border-blue-100">
								<span class="material-symbols-outlined text-blue-600 text-xl">info</span>
								<div>* Please include your Registration No. <span class="font-bold">${order.reg_no}</span> in the transfer notes/remarks.</div>
							</div>
						</div>
					</div>

					<div class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-10 text-center relative hover:bg-gray-50 hover:border-blue-400 transition-colors cursor-pointer group">
						<input type="file" id="receipt-upload-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/jpeg,image/png,application/pdf">
						<div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
							<span class="material-symbols-outlined text-3xl text-blue-600">cloud_upload</span>
						</div>
						<p class="font-bold text-gray-900">Click or drag to upload receipt<br>点击上传汇款凭证</p>
						<p class="text-xs text-gray-500 mt-2">支持 JPG, PNG, PDF (最大 5MB)</p>
						<p id="upload-file-name" class="mt-4 text-sm text-blue-600 font-bold hidden"></p>
					</div>

					<div class="mt-8">
						<button id="btn-submit-receipt" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all disabled:opacity-50">
							Submit Receipt 提交凭证
						</button>
					</div>
				</main>
				`;
			}

			// Success State
			let subtitle = "Your registration is confirmed. 您的席位已预留。";
			if (isBank) subtitle = "We have received your receipt and will verify it shortly. 我们已收到您的汇款凭证，将尽快核实。";
			if (this.state.paymentMethod === 'onsite') subtitle = "Please complete the payment at the registration desk. 请在会议现场完成缴费。";

			return `
				<header class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-sm flex justify-center py-4">
					<span class="text-xl font-bold tracking-tighter text-blue-600">Event Registration</span>
				</header>
				<main class="pt-24 pb-32 px-6 max-w-md mx-auto min-h-screen flex flex-col items-center">
					<div class="flex flex-col items-center text-center mb-12">
						<div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-6 shadow-sm border border-green-200">
							<span class="material-symbols-outlined text-5xl font-bold">check_circle</span>
						</div>
						<h1 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Success 报名成功!</h1>
						<p class="text-gray-500 leading-relaxed max-w-sm">${subtitle}</p>
					</div>

					<div class="w-full bg-white rounded-2xl p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden text-center">
						<div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -z-10 opacity-50"></div>
						<p class="text-xs text-gray-500 uppercase tracking-widest font-semibold mb-2">REG NO. 登记号</p>
						<p class="text-4xl font-black text-blue-600 tracking-tighter">${order.reg_no}</p>
						
						<div class="mt-8 pt-6 border-t border-gray-100">
							<p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">NAME 姓名</p>
							<p class="text-xl font-bold text-gray-900">${primary.name}</p>
							<p class="text-sm text-gray-500 mt-1">${primary.company || ''}</p>
						</div>
					</div>

					<div class="mt-12 w-full text-center">
						<button class="text-blue-600 font-bold hover:underline py-3 px-6 rounded-lg hover:bg-blue-50 transition-colors" onclick="window.location.reload()">Start New Registration</button>
					</div>
				</main>
			`;
		}

		bindEvents() {
			// Inputs
			this.mountPoint.querySelectorAll('.input-field').forEach(input => {
				input.addEventListener('input', (e) => {
					const index = parseInt(e.target.dataset.index, 10);
					const field = e.target.dataset.field;
					this.updateAttendee(index, field, e.target.value);
				});
			});

			// Add Attendee
			const btnAdd = this.mountPoint.querySelector('#btn-add-attendee');
			if (btnAdd) btnAdd.addEventListener('click', () => this.addAttendee());

			// Remove Attendee
			this.mountPoint.querySelectorAll('.action-remove-attendee').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const index = parseInt(e.target.dataset.index, 10);
					this.removeAttendee(index);
				});
			});

			// Tickets Selection
			this.mountPoint.querySelectorAll('.ticket-radio').forEach(radio => {
				radio.addEventListener('change', (e) => {
					this.state.ticketId = e.target.value;
				});
			});

			// Payment Method Selection
			this.mountPoint.querySelectorAll('.payment-radio').forEach(radio => {
				radio.addEventListener('change', (e) => {
					this.state.paymentMethod = e.target.value;
					this.render(); // Re-render to update UI checked state visually
				});
			});

			// Submit Order
			const btnSubmit = this.mountPoint.querySelector('#btn-submit-order');
			if (btnSubmit) btnSubmit.addEventListener('click', () => this.submitOrder());

			// Receipt Upload Change
			const uploadInput = this.mountPoint.querySelector('#receipt-upload-input');
			if (uploadInput) {
				uploadInput.addEventListener('change', (e) => {
					const fileNameLabel = this.mountPoint.querySelector('#upload-file-name');
					if (e.target.files.length > 0) {
						fileNameLabel.textContent = e.target.files[0].name;
						fileNameLabel.classList.remove('hidden');
					}
				});
			}

			// Receipt Upload Submit
			const btnReceipt = this.mountPoint.querySelector('#btn-submit-receipt');
			if (btnReceipt) btnReceipt.addEventListener('click', () => this.uploadReceipt());

			// Next Step
			const btnNext = this.mountPoint.querySelector('#btn-next-step');
			if (btnNext) btnNext.addEventListener('click', () => this.nextStep());

			// Generic Next / Prev
			this.mountPoint.querySelectorAll('.action-prev').forEach(btn => btn.addEventListener('click', () => this.prevStep()));
			this.mountPoint.querySelectorAll('.action-next').forEach(btn => btn.addEventListener('click', () => this.nextStep()));
		}

		async submitOrder() {
			const btn = this.mountPoint.querySelector('#btn-submit-order');
			if (btn) {
				btn.disabled = true;
				btn.innerHTML = `<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Processing...`;
			}

			try {
				const response = await fetch(`${this.config.apiUrl}/order/create`, {
					method: 'POST',
					headers: { 
						'Content-Type': 'application/json',
						'X-WP-Nonce': this.config.nonce
					},
					body: JSON.stringify({
						attendees: this.state.attendees,
						ticket_id: this.state.ticketId,
						payment_method: this.state.paymentMethod
					})
				});
				
				const data = await response.json();
				if (!response.ok) throw new Error(data.message || 'Error occurred while submitting order');

				this.state.orderData = data;
				
				if (this.state.paymentMethod === 'wechat' && data.payment_data && data.payment_data.payment_type === 'h5') {
					// Redirect to WeChat H5
					window.location.href = data.payment_data.mweb_url;
				} else {
					this.state.step = 4;
					window.scrollTo(0, 0);
					this.render();
				}
			} catch (error) {
				alert('Order submission failed: ' + error.message);
				this.render(); // Reset button state
			}
		}

		async uploadReceipt() {
			const fileInput = this.mountPoint.querySelector('#receipt-upload-input');
			if (!fileInput.files.length) {
				alert('Please select a file to upload.');
				return;
			}

			const btn = this.mountPoint.querySelector('#btn-submit-receipt');
			btn.disabled = true;
			btn.innerHTML = `<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white inline-block align-middle mr-2"></div> Uploading...`;

			const formData = new FormData();
			formData.append('receipt', fileInput.files[0]);
			formData.append('order_id', this.state.orderData.order_id);

			try {
				const response = await fetch(`${this.config.apiUrl}/order/upload-receipt`, {
					method: 'POST',
					headers: { 
						'X-WP-Nonce': this.config.nonce
						// Note: Do not stringify body or set Content-Type for FormData, browser handles it.
					},
					body: formData
				});
				
				const data = await response.json();
				if (!response.ok) throw new Error(data.message || 'Error occurred while uploading receipt');

				this.state.receiptUploaded = true;
				window.scrollTo(0, 0);
				this.render();
			} catch (error) {
				alert('Upload failed: ' + error.message);
				this.render(); // Reset button state
			}
		}
	}

	document.addEventListener('DOMContentLoaded', () => {
		new ConfRegistrationApp(window.confAppConfig);
	});
})();

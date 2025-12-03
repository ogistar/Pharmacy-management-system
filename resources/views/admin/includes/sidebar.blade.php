<!-- Sidebar -->
<div class="sidebar" id="sidebar">
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			
			<ul>
				<li class="menu-title"><span>{{ __('menu.main') }}</span></li>
				<li class="{{ route_is('dashboard') ? 'active' : '' }}">
					<a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> <span>{{ __('menu.dashboard') }}</span></a>
				</li>

				<li class="menu-title"><span>{{ __('menu.sales_section') }}</span></li>
				<li class="submenu {{ route_is('pos.*') || route_is('receivables.*') || route_is('cash-sessions.*') ? 'active' : '' }}">
					<a href="#"><i class="fas fa-shopping-cart"></i> <span>{{ __('menu.sales') }}</span> <span class="menu-arrow"></span></a>
					<ul>
						<li class="{{ route_is('pos.*') ? 'active' : '' }}">
							<a href="{{ route('pos.index') }}">
								<i class="fas fa-cash-register"></i> <span>{{ __('menu.pos') }}</span>
							</a>
						</li>
						<li class="{{ route_is('receivables.*') ? 'active' : '' }}">
							<a href="{{ route('receivables.index') }}">
								<i class="fas fa-file-invoice-dollar"></i> <span>{{ __('menu.receivables') }}</span>
							</a>
						</li>
						<li class="{{ route_is('cash-sessions.*') ? 'active' : '' }}">
							<a href="{{ route('cash-sessions.index') }}">
								<i class="fas fa-stopwatch"></i> <span>{{ __('menu.cash_sessions') }}</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="menu-title"><span>{{ __('menu.patients_prescriptions') }}</span></li>
				<li class="submenu {{ route_is('patients.*') || route_is('prescriptions.*') || route_is('compounds.*') ? 'active' : '' }}">
					<a href="#"><i class="fas fa-notes-medical"></i> <span>{{ __('menu.patients_prescriptions') }}</span> <span class="menu-arrow"></span></a>
					<ul>
						<li class="{{ route_is('patients.*') ? 'active' : '' }}">
							<a href="{{ route('patients.index') }}"><i class="fas fa-user-check"></i> <span>{{ __('menu.patients') }}</span></a>
						</li>
						<li class="{{ route_is('prescriptions.*') ? 'active' : '' }}">
							<a href="{{ route('prescriptions.index') }}"><i class="fas fa-file-medical"></i> <span>{{ __('menu.prescriptions') }}</span></a>
						</li>
						<li class="{{ route_is('compounds.*') ? 'active' : '' }}">
							<a href="{{ route('compounds.index') }}"><i class="fas fa-prescription-bottle-alt"></i> <span>{{ __('menu.compounds') }}</span></a>
						</li>
					</ul>
				</li>

				<li class="menu-title"><span>{{ __('menu.inventory_products') }}</span></li>
				@can('view-category')
				<li class="{{ route_is('categories.*') ? 'active' : '' }}">
					<a href="{{ route('categories.index') }}"><i class="fas fa-th-large"></i> <span>{{ __('menu.categories') }}</span></a>
				</li>
				@endcan

				@can('view-products')
				<li class="submenu {{ route_is('products.*') || route_is('outstock') || route_is('expired') ? 'active' : '' }}">
					<a href="#"><i class="fas fa-boxes"></i> <span>{{ __('menu.products') }}</span> <span class="menu-arrow"></span></a>
					<ul>
						<li class="{{ route_is('products.*') ? 'active' : '' }}">
							<a href="{{ route('products.index') }}">
								<i class="fas fa-list-ul"></i> <span>{{ __('menu.products') }}</span>
							</a>
						</li>
						@can('view-outstock-products')
						<li class="{{ route_is('outstock') ? 'active' : '' }}">
							<a href="{{ route('outstock') }}">
								<i class="fas fa-box-open"></i> <span>{{ __('menu.outstock') }}</span>
							</a>
						</li>
						@endcan
						@can('view-expired-products')
						<li class="{{ route_is('expired') ? 'active' : '' }}">
							<a href="{{ route('expired') }}">
								<i class="fas fa-exclamation-circle"></i> <span>{{ __('menu.expired') }}</span>
							</a>
						</li>
						@endcan
					</ul>
				</li>
				@endcan

				@can('view-products')
				<li class="{{ route_is('batches.*') ? 'active' : '' }}">
					<a href="{{ route('batches.index') }}"><i class="fas fa-layer-group"></i> <span>{{ __('menu.batches') }}</span></a>
				</li>
				<li class="{{ route_is('stock-opnames.*') ? 'active' : '' }}">
					<a href="{{ route('stock-opnames.index') }}"><i class="fas fa-clipboard-check"></i> <span>{{ __('menu.stock_opname') }}</span></a>
				</li>
				@endcan

				<li class="{{ route_is('stock-tools.index') ? 'active' : '' }}">
					<a href="{{ route('stock-tools.index') }}"><i class="fas fa-undo-alt"></i> <span>{{ __('menu.returns_stock') }}</span></a>
				</li>

				@canany(['view-purchase', 'view-supplier'])
				<li class="submenu {{ route_is('purchases.*') || route_is('suppliers.*') ? 'active' : '' }}">
					<a href="#"><i class="fas fa-shopping-basket"></i> <span>{{ __('menu.purchase') }}</span> <span class="menu-arrow"></span></a>
					<ul>
						@can('view-purchase')
						<li class="{{ route_is('purchases.*') ? 'active' : '' }}">
							<a href="{{ route('purchases.index') }}">
								<i class="fas fa-receipt"></i> <span>{{ __('menu.purchase') }}</span>
							</a>
						</li>
						@endcan
						@can('view-supplier')
						<li class="{{ route_is('suppliers.*') ? 'active' : '' }}">
							<a href="{{ route('suppliers.index') }}">
								<i class="fas fa-truck"></i> <span>{{ __('menu.supplier') }}</span>
							</a>
						</li>
						@endcan
					</ul>
				</li>
				@endcanany

				<li class="menu-title"><span>Layanan</span></li>
				<li class="{{ route_is('patients.*') ? 'active' : '' }}">
					<a href="{{ route('patients.index') }}"><i class="fas fa-user-check"></i> <span>{{ __('menu.patients') }}</span></a>
				</li>
				<li class="{{ route_is('prescriptions.*') ? 'active' : '' }}">
					<a href="{{ route('prescriptions.index') }}"><i class="fas fa-file-medical"></i> <span>{{ __('menu.prescriptions') }}</span></a>
				</li>

				@can('view-reports')
				<li class="menu-title"><span>Laporan</span></li>
				<li class="submenu {{ route_is('sales.report') || route_is('purchases.report') ? 'active' : '' }}">
					<a href="#"><i class="fas fa-chart-pie"></i> <span>{{ __('menu.reports') }}</span> <span class="menu-arrow"></span></a>
					<ul>
						<li class="{{ route_is('sales.report') ? 'active' : '' }}">
							<a href="{{ route('sales.report') }}">
								<i class="fas fa-chart-line"></i> <span>{{ __('menu.sale_report') }}</span>
							</a>
						</li>
						<li class="{{ route_is('purchases.report') ? 'active' : '' }}">
							<a href="{{ route('purchases.report') }}">
								<i class="fas fa-chart-bar"></i> <span>{{ __('menu.purchase_report') }}</span>
							</a>
						</li>
					</ul>
				</li>
				@endcan

				<li class="menu-title"><span>Pengaturan</span></li>
				@can('view-access-control')
				<li class="submenu {{ route_is('permissions.index') || route_is('roles.*') ? 'active' : '' }}">
					<a href="#"><i class="fas fa-lock"></i> <span>{{ __('menu.access_control') }}</span> <span class="menu-arrow"></span></a>
					<ul>
						@can('view-permission')
						<li class="{{ route_is('permissions.index') ? 'active' : '' }}">
							<a href="{{ route('permissions.index') }}">
								<i class="fas fa-shield-alt"></i> <span>{{ __('menu.permissions') }}</span>
							</a>
						</li>
						@endcan
						@can('view-role')
						<li class="{{ route_is('roles.*') ? 'active' : '' }}">
							<a href="{{ route('roles.index') }}">
								<i class="fas fa-user-shield"></i> <span>{{ __('menu.roles') }}</span>
							</a>
						</li>
						@endcan
					</ul>
				</li>
				@endcan

				@can('view-users')
				<li class="{{ route_is('users.*') ? 'active' : '' }}">
					<a href="{{ route('users.index') }}"><i class="fas fa-users"></i> <span>{{ __('menu.users') }}</span></a>
				</li>
				@endcan
				
				<li class="{{ route_is('profile') ? 'active' : '' }}">
					<a href="{{ route('profile') }}"><i class="fas fa-user-circle"></i> <span>{{ __('menu.profile') }}</span></a>
				</li>
				<li class="{{ route_is('backup.index') ? 'active' : '' }}">
					<a href="{{ route('backup.index') }}"><i class="fas fa-cloud-upload-alt"></i> <span>{{ __('menu.backup') }}</span></a>
				</li>
				@can('view-settings')
				<li class="{{ route_is('settings') ? 'active' : '' }}">
					<a href="{{ route('settings') }}">
						<i class="fas fa-cog"></i>
						<span>{{ __('menu.settings') }}</span>
					</a>
				</li>
				@endcan
			</ul>
		</div>
	</div>
</div>
<!-- /Sidebar -->

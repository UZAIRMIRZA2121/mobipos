// ============================================================
// DASHBOARD
// ============================================================
async function renderDashboard(period = 'week') {
  const prods = store.get('products') || [];

  if (document.getElementById('dashTotalEarning')) {
    try {
      const stats = await api('/shop/api/dashboard-stats?period=' + period);

      let titleSuffix = '(Last 7 Days)';
      if (period === 'month') titleSuffix = '(Last 30 Days)';
      if (period === 'year') titleSuffix = '(Last 12 Months)';
      const titleEl = document.getElementById('dailySalesTitle');
      if (titleEl) titleEl.textContent = 'Daily Sales ' + titleSuffix;

      document.getElementById('dashTotalEarning').textContent = fmtCur(stats.total_earning);
      document.getElementById('dashTotalExpense').textContent = fmtCur(stats.total_expense);
      document.getElementById('dashActualEarning').textContent = fmtCur(stats.actual_earning);
      document.getElementById('dashStockValue').textContent = fmtCur(stats.stock_value);

      // Populate Top Selling Products
      const topTbody = document.getElementById('dashTopProductsTbody');
      if (topTbody) {
        if (stats.top_products && stats.top_products.length > 0) {
          topTbody.innerHTML = stats.top_products.map(p => `
                      <tr>
                          <td>
                              <div style="font-weight:500">${p.name}</div>
                              ${p.imei ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono)">IMEI: ${p.imei}</div>` : ''}
                          </td>
                          <td class="text-right">${p.total_qty}</td>
                          <td class="text-right" style="font-weight:600">${fmtCur(p.total_revenue)}</td>
                      </tr>
                  `).join('');
        } else {
          topTbody.innerHTML = '<tr><td colspan="3" class="empty-cell">No sales data available</td></tr>';
        }
      }

      // Populate Recent Sales
      const salesTbody = document.getElementById('dashRecentSalesTbody');
      if (salesTbody) {
        if (stats.recent_sales && stats.recent_sales.length > 0) {
          salesTbody.innerHTML = stats.recent_sales.map(s => {
            let statusBadge = '';
            if (s.payment_status === 'paid') statusBadge = '<span class="badge badge-success">PAID</span>';
            else if (s.payment_status === 'partial') statusBadge = '<span class="badge badge-warning">PARTIAL</span>';
            else if (s.payment_status === 'refunded') statusBadge = '<span class="badge badge-danger">REFUNDED</span>';
            else statusBadge = '<span class="badge badge-danger">UNPAID</span>';

            return `
                      <tr>
                          <td>#${s.id}</td>
                          <td>${s.buyer ? s.buyer.name : 'Walk-in'}</td>
                          <td class="text-right" style="font-weight:600">${fmtCur(s.total)}</td>
                          <td class="text-right">${statusBadge}</td>
                      </tr>
                      `;
          }).join('');
        } else {
          salesTbody.innerHTML = '<tr><td colspan="4" class="empty-cell">No recent sales</td></tr>';
        }
      }

      // Render Daily Sales Chart
      if (document.getElementById('dailySalesChart') && stats.daily_sales) {
        const dates = stats.daily_sales.map(s => s.date);
        const totals = stats.daily_sales.map(s => parseFloat(s.total_sales));
        const profits = stats.daily_sales.map(s => parseFloat(s.net_profit || 0));

        var options = {
          series: [{
            name: 'Total Sales',
            data: totals
          }, {
            name: 'Net Profit',
            data: profits
          }],
          chart: {
            height: 350,
            type: 'area',
            fontFamily: 'inherit',
            toolbar: { show: false }
          },
          dataLabels: { enabled: false },
          stroke: { curve: 'smooth', width: 2 },
          xaxis: {
            categories: dates,
            type: 'datetime'
          },
          yaxis: {
            labels: {
              formatter: function (value) {
                return "PKR " + value.toLocaleString();
              }
            }
          },
          tooltip: { x: { format: 'dd MMM yyyy' } },
          fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.9, stops: [0, 90, 100] }
          },
          colors: ['#0f172a', '#10b981']
        };

        var chartEl = document.querySelector("#dailySalesChart");
        if (window.dailySalesApexChart) {
          window.dailySalesApexChart.destroy();
        }
        window.dailySalesApexChart = new ApexCharts(chartEl, options);
        window.dailySalesApexChart.render();
      }

    } catch (e) {
      console.error('Failed to load dashboard stats', e);
    }

    // Get 5 most recent products
    const recent = [...prods].reverse().slice(0, 5);

    const recentTbody = document.getElementById('dashRecentProdsTbody');
    if (recentTbody) {
      recentTbody.innerHTML = recent.length ?
        recent.map(p => `
              <tr>
                <td>
                  <div style="font-weight:500">${p.name}</div>
                  ${p.imei ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono)">IMEI/SN: ${p.imei}</div>` : ''}
                </td>
                <td style="text-transform:capitalize">${p.type}</td>
                <td style="text-transform:capitalize">${p.condition}</td>
                <td class="text-right" style="font-weight:600">${fmtCur(p.sale)}</td>
                <td class="text-right">
                  ${getProdBadge(p)}
                </td>
              </tr>
            `).join('') : '<tr><td colspan="5" class="empty-cell">No products added yet</td></tr>';
    }
  }
}


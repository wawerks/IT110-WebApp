<template>
  <v-container>
    <!-- Overview Cards -->
    <v-row>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Total Users</v-card-title>
          <v-card-text class="stat-value">{{ stats.totalUsers }}</v-card-text>
          <v-icon color="white" large>mdi-account-multiple</v-icon>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Lost Items</v-card-title>
          <v-card-text class="stat-value">{{ stats.lostItems }}</v-card-text>
          <v-icon color="white" large>mdi-cart</v-icon>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Found Items</v-card-title>
          <v-card-text class="stat-value">{{ stats.foundItems }}</v-card-text>
          <v-icon color="white" large>mdi-trending-up</v-icon>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="card-overview" outlined>
          <v-card-title>Total Claims</v-card-title>
          <v-card-text class="stat-value">{{ stats.claims }}</v-card-text>
          <v-icon color="white" large>mdi-bell</v-icon>
        </v-card>
      </v-col>
    </v-row>

    <!-- Pie Chart -->
    <v-row>
      <v-col cols="12" sm="6">
        <v-card outlined>
          <v-card-title>Item Statistics</v-card-title>
          <div ref="pieChart" class="chart-container"></div>
        </v-card>
      </v-col>

      <!-- Bar Graph -->
      <v-col cols="12" sm="6">
        <v-card outlined>
          <v-card-title>Item Overview</v-card-title>
          <div ref="barChart" class="chart-container"></div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import * as d3 from 'd3';

export default {
  name: 'Dashboard',
  data() {
    return {
      stats: {
        totalUsers: 0,
        lostItems: 0,
        foundItems: 0,
        claims: 0,
      },
      pieChartData: [0, 0, 0], // Data for the pie chart
      barChartData: [0, 0, 0, 0], // Data for the bar chart
    };
  },
  mounted() {
    this.fetchDashboardData();
  },
  methods: {
    async fetchDashboardData() {
      try {
        const response = await fetch('/admin/dashboard');
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Update stats
        this.stats = {
          totalUsers: data.totalUsers,
          lostItems: data.lostItems,
          foundItems: data.foundItems,
          claims: data.claims,
        };

        // Update chart data
        this.pieChartData = [data.lostItems, data.foundItems, data.claims];
        this.barChartData = [data.totalUsers, data.lostItems, data.foundItems, data.claims];

        // Render the charts with D3.js
        this.renderPieChart();
        this.renderBarChart();
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    },

    renderPieChart() {
      const data = this.pieChartData;
      const width = 300;
      const height = 300;
      const radius = Math.min(width, height) / 2;

      const svg = d3.select(this.$refs.pieChart)
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .append('g')
        .attr('transform', `translate(${width / 2}, ${height / 2})`);

      const color = d3.scaleOrdinal(['#FF6384', '#36A2EB', '#FFCE56']);
      const categories = ['Lost Items', 'Found Items', 'Claims'];

      const pie = d3.pie();
      const arc = d3.arc().outerRadius(radius).innerRadius(0);

      // Tooltip
      const tooltip = d3.select(this.$refs.pieChart)
        .append('div')
        .style('position', 'absolute')
        .style('visibility', 'hidden')
        .style('background', '#fff')
        .style('border', '1px solid #ccc')
        .style('padding', '5px')
        .style('border-radius', '4px')
        .style('box-shadow', '0 4px 6px rgba(0, 0, 0, 0.1)');

      const pieData = pie(data);

      const arcs = svg.selectAll('.arc')
        .data(pieData)
        .enter()
        .append('g')
        .attr('class', 'arc');

      // Append paths for the slices
      arcs.append('path')
        .attr('d', arc)
        .style('fill', (d, i) => color(i))
        .on('mouseover', (event, d) => {
          tooltip.style('visibility', 'visible').text(`${categories[d.index]}: ${d.data}`);
        })
        .on('mousemove', (event) => {
          tooltip.style('top', `${event.pageY - 10}px`).style('left', `${event.pageX + 10}px`);
        })
        .on('mouseout', () => {
          tooltip.style('visibility', 'hidden');
        });

      // Append labels for the slices
      arcs.append('text')
        .attr('transform', (d) => {
          const [x, y] = arc.centroid(d); // Get the center of each slice
          return `translate(${x}, ${y})`;  // Position the label at the center
        })
        .attr('text-anchor', 'middle')
        .attr('dy', '.35em') // Adjust the vertical alignment
        .style('font-size', '14px')
        .style('fill', '#fff')
        .text((d, i) => `${categories[i]}: ${d.data}`); // Display category and value
    },

    renderBarChart() {
      const data = this.barChartData;
      const width = 500;
      const height = 300;
      const margin = { top: 20, right: 30, bottom: 40, left: 40 };
      const chartWidth = width - margin.left - margin.right;
      const chartHeight = height - margin.top - margin.bottom;

      const svg = d3.select(this.$refs.barChart)
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

      const x = d3.scaleBand()
        .domain(data.map((_, i) => i))
        .range([0, chartWidth])
        .padding(0.1);

      const y = d3.scaleLinear()
        .domain([0, d3.max(data)])
        .nice()
        .range([chartHeight, 0]);

      const tooltip = d3.select(this.$refs.barChart)
        .append('div')
        .style('position', 'absolute')
        .style('visibility', 'hidden')
        .style('background', '#fff')
        .style('border', '1px solid #ccc')
        .style('padding', '5px')
        .style('border-radius', '4px')
        .style('box-shadow', '0 4px 6px rgba(0, 0, 0, 0.1)');

      svg.selectAll('.bar')
        .data(data)
        .enter()
        .append('rect')
        .attr('class', 'bar')
        .attr('x', (d, i) => x(i))
        .attr('y', d => y(d))
        .attr('width', x.bandwidth())
        .attr('height', d => chartHeight - y(d))
        .style('fill', '#4CAF50')
        .on('mouseover', (event, d) => {
          tooltip.style('visibility', 'visible').text(`Value: ${d}`);
        })
        .on('mousemove', (event) => {
          tooltip.style('top', `${event.pageY - 10}px`).style('left', `${event.pageX + 10}px`);
        })
        .on('mouseout', () => {
          tooltip.style('visibility', 'hidden');
        });

      svg.append('g')
        .attr('class', 'x axis')
        .attr('transform', `translate(0,${chartHeight})`)
        .call(d3.axisBottom(x).tickFormat(i => ['Total Users', 'Lost Items', 'Found Items', 'Claims'][i]));

      svg.append('g')
        .attr('class', 'y axis')
        .call(d3.axisLeft(y));
    },
  },
};
</script>


<style scoped>
.card-overview {
  padding: 15px;
  text-align: center;
  background-color: #FFFFFF;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  color: #333333;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #181C14;
}

.chart-container {
  width: 100%;
  height: 300px;
}
</style>

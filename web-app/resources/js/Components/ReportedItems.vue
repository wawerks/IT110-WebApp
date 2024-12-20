<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-card-title class="headline d-flex align-center">
        <v-spacer></v-spacer>
        <span>Reported Items</span>
        <v-spacer></v-spacer>
      </v-card-title>
      <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line hide-details class="mx-4"
        style="max-width: 300px;"></v-text-field>
      <v-divider></v-divider>
      <v-data-table
        :headers="headers"
        :items="formattedItems"
        :search="search"
        :loading="loading"
        :items-per-page="10"
        class="elevation-1"
        v-model:page="page"
      >
        <template v-slot:header.item_name>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Item Name</div>
            <div class="text-caption text-grey">Name of reported item</div>
          </div>
        </template>

        <template v-slot:header.user.name>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Reporter</div>
            <div class="text-caption text-grey">Person who reported</div>
          </div>
        </template>

        <template v-slot:header.status>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Status</div>
            <div class="text-caption text-grey">Current state</div>
          </div>
        </template>

        <template v-slot:header.type>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Type</div>
            <div class="text-caption text-grey">Item category</div>
          </div>
        </template>

        <template v-slot:header.created_at>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Date</div>
            <div class="text-caption text-grey">When reported</div>
          </div>
        </template>


        <template v-slot:progress>
          <v-progress-linear color="#4fb9af" height="2" indeterminate></v-progress-linear>
        </template>
        <template v-slot:bottom="props">
          <div class="d-flex align-center justify-center pa-4 gap-4">
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat"
              :disabled="page === 1" @click="page--">
              Previous
            </v-btn>
            <span>Page {{ page }} of {{ Math.ceil(formattedItems.length / 10) }}</span>
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat"
              :disabled="page >= Math.ceil(formattedItems.length / 10)" @click="page++">
              Next
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReportedItems',
  data() {
    return {
      search: '',
      page: 1,
      headers: [
        {
          text: 'Item Name',
          value: 'item_name',
          width: '25%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        {
          text: 'Reporter',
          value: 'user.name',
          width: '20%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        {
          text: 'Type',
          value: 'type',
          width: '15%',
          align: 'start',
          sortable: true,
        },
        {
          text: 'Time',
          value: 'created_at',
          width: '15%',
          align: 'start',
          sortable: true,
        },
      ],
      items: [],
      loading: true,
    };
  },
  computed: {
    formattedItems() {
      return this.items
        .map(item => ({
          ...item,
          type: item.isLost !== undefined ? (item.isLost ? 'Lost Item' : 'Found Item') : (item.fromLostItems ? 'Lost Item' : 'Found Item'),
          created_at: this.formatDate(item.created_at),
        }))
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    },
  },
  created() {
    this.fetchItems();
  },
  methods: {
    fetchItems() {
      this.loading = true;
      axios.get('/admin/reported-items')
        .then(response => {
          this.items = [
            ...response.data.lostItems.map(item => ({ ...item, fromLostItems: true })),
            ...response.data.foundItems.map(item => ({ ...item, fromLostItems: false }))
          ];
        })
        .catch(error => {
          console.error('Error fetching reported items:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    formatDate(dateString) {
      const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
        hour12: true
      };
      const date = new Date(dateString);
      return date.toLocaleString('en-US', options);
    },
  },
};
</script>

<style scoped>
.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}

:deep(.v-data-table-header th) {
  color: black !important;
  font-weight: bold !important;
  font-size: 1rem !important;
  text-transform: none !important;
  background-color: #f5f5f5 !important;
  padding: 12px 16px !important;
}

:deep(.v-data-table tbody td) {
  color: black !important;
  padding: 12px 16px !important;
}

:deep(.v-data-table tbody tr:nth-child(even)) {
  background-color: #f9f9f9;
}

:deep(.v-data-table tbody tr:hover) {
  background-color: #f0f0f0;
}
</style>

<template>
  <div>
    <div class="container">
      <div class="row">
        <div class="col-12">
          <form v-on:submit.prevent="GetRestaurants(true)">
            <input type="text" v-model="textSearch" placeholder="Search" />
            <button class="btn btn-primary" type="submit">Search</button>
            <div v-if="loading" class="spinner-border text-danger" role="status" ref="loading">
              <span class="sr-only">Loading...</span>
            </div>
            <!-- <button class="btn btn-success" type="button" @click="GetRestaurants(true)">NextPage</button> -->
          </form>
        </div>
      </div>
      <br />
    </div>
    <!-- <Table :datas="datas" :headerTables="headerTables" /> -->
    <Map :center="lat_lng" :zoom="14" :style="{width:window_WH.w,height:window_WH.h}">
      <GmapMarker
        :key="index"
        v-for="(m, index) in datas"
        :position="m.location"
        :clickable="true"
        @click="goto(m.name)"
        :title="m.address"
        :label="m.name"
      ></GmapMarker>
    </Map>
  </div>
</template>

<script>
import Vue from "vue";
import axios from "axios";
import Table from "./Table";
import VueGoogleMaps, { Map, Marker } from "vue2-google-maps";

export default {
  components: {
    Table,
    Map,
    GmapMarker: Marker
  },

  data() {
    return {
      lat_lng: { lat: 13.736717, lng: 100.523186 },
      textSearch: "Bang sue",
      datas: [],
      headerTables: ["No.", "Name", "Address", "lat", "lng"],
      next_page_token: "",
      id: 1,
      window_WH: { w: 0, h: 0 },
      loading: false
    };
  },

  mounted() {
    this.GetRestaurants();
  },

  created() {
    axios.interceptors.request.use(config => {
      this.loading = true;
      return config;
    });
    axios.interceptors.response.use(response => {
      this.loading = false;
      return response;
    });
    window.addEventListener("resize", this.onResize);
    this.onResize();
  },

  methods: {
    GetRestaurants: function(search) {
      if (search) this.datas = [];
      let url = "api/restaurants";
      this.textSearch ? (url += "?place=" + this.textSearch) : null;
      this.textSearch && this.next_page_token
        ? (url += "&next_page_token=" + this.next_page_token)
        : null;
      axios.get(url).then((res, err) => {
        console.log(res.data);
        if (!err) {
          if (res.status === 200) {
            // console.log(res.data.results);
            let _id = this.id;
            const _datas = res.data.results.map(x => ({
              id: _id++,
              address: x.formatted_address,
              name: x.name,
              location: {
                lat: x.geometry.location.lat,
                lng: x.geometry.location.lng
              }
            }));
            this.datas = [...this.datas, ..._datas];
            this.id = res.data.next_page_token ? _id : 1;
            this.next_page_token = res.data.next_page_token
              ? res.data.next_page_token
              : "";
            if (res.data.next_page_token) {
              setTimeout(() => this.GetRestaurants(), 1000);
            } else {
              this.lat_lng.lat = this.datas[0].location.lat;
              this.lat_lng.lng = this.datas[0].location.lng;
            }
          } else {
            alert(res.statusText);
          }
        } else {
          alert(err);
        }
      });
    },
    onResize() {
      this.window_WH.w = window.innerWidth + "px";
      this.window_WH.h = window.innerHeight - 62 + "px";
    },
    goto(name) {
      window.open("https://www.google.co.th/maps/place/" + name, "_blank");
    }
  }
};
</script>

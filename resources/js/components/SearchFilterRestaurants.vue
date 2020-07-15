<template>
  <div>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-auto">
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
        :title="m.formatted_address"
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
      lat_lng: { lat: 13.736717, lng: 100.523186 }, // latitude longitude default is thailand
      textSearch: "Bang sue", //text search default
      datas: [], //arrObj show in map
      headerTables: ["No.", "Name", "Address", "lat", "lng"],
      next_page_token: "",
      next_page_token_backup: "",
      // id: 1,
      window_WH: { w: 0, h: 0 }, //width height browser
      loading: false
    };
  },

  mounted() {
    this.GetRestaurants();
  },

  created() {
    //set spinner on request api && add event resize browser
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
      //call api laravel for get place datas
      if (search) this.datas = [];
      let url = "api/restaurants";
      this.next_page_token_backup = this.next_page_token;
      this.textSearch && (url += "?place=" + this.textSearch);
      this.textSearch &&
        this.next_page_token &&
        (url += "&next_page_token=" + this.next_page_token);

      axios.get(url).then((res, err) => {
        if (!err) {
          // console.log(res.data);
          if (res.data.datas.length && res.data.status !== "INVALID_REQUEST") {
            // let _id = this.id;
            // this.id = res.data.next_page_token ? _id : 1;
            this.next_page_token = res.data.next_page_token //set next_page_token if response has
              ? res.data.next_page_token
              : "";
            if (res.data.next_page_token) {
              //call for stack datas if response has param next_page_token
              setTimeout(() => this.GetRestaurants(), 1000);
            } else {
              this.datas = [...this.datas, ...res.data.datas]; //set datas
              this.lat_lng.lat = this.datas[0].location.lat; //set warp map is request is last
              this.lat_lng.lng = this.datas[0].location.lng;
            }
          } else {
            this.next_page_token = this.next_page_token_backup; //call for stack datas if response fail
            setTimeout(() => this.GetRestaurants(), 1000);
          }
        }
      });
    },
    onResize() {
      // set size map equal browser
      this.window_WH.w = window.innerWidth + "px";
      this.window_WH.h = window.innerHeight - 62 + "px";
    },
    goto(name) {
      //link new tap to google map and set default search
      window.open("https://www.google.co.th/maps/place/" + name, "_blank");
    }
  }
};
</script>

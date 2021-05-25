<template>
  <div>
    <spin v-if="loading" />
    <div v-else-if="!loading && !not_found">
      <h1>
        <b> {{ post.country }} </b>
        Covid-19 Statistics
      </h1>
      <div id="cards_landscape_wrap-2">
        <div class="container">
          <div class="row">
            <post
              v-model="post"
              :title="'Total confirmed cases'"
              :statics="post.total_confirmed"
              :date="post.date"
            />
            <post
              v-model="post"
              :title="'New cases daily'"
              :statics="post.new_cases"
              :date="post.owid_date"
            />
            <post
              v-model="post"
              :title="'Total deaths'"
              :statics="post.total_deaths"
              :date="post.date"
            />
            <post
              v-model="post"
              :title="'New deaths daily'"
              :statics="post.new_deaths"
              :date="post.owid_date"
            />
            <post
              v-model="post"
              :title="'Total recovered'"
              :statics="post.total_recovered"
              :date="post.date"
            />
            <post
              v-model="post"
              :title="'Total active cases'"
              :statics="post.active_cases"
              :date="post.date"
            />
            <post
              v-model="post"
              :title="'People tested daily'"
              :statics="post.tested"
              :date="post.date_cumulative"
            />
            <post
              v-model="post"
              :title="'Total people tested'"
              :statics="post.total_tested"
              :date="post.date_cumulative"
            />
            <post
              v-model="post"
              :title="'People vaccinated daily'"
              :statics="post.vaccniated"
              :date="post.vaccniated_date"
            />
            <post
              v-model="post"
              :title="'People fully vaccinated'"
              :statics="post.fully_vaccinated"
              :date="post.fully_vaccinated_date"
            />
            <post
              v-model="post"
              :title="'Total people vaccinated'"
              :statics="post.total_vaccinated"
              :date="post.total_vaccinated_date"
            />
          </div>
        </div>
      </div>
    </div>
    <div uk-alert v-if="not_found">
      <a class="uk-alert-close" uk-close></a>
      <h3>404 error</h3>
    </div>
  </div>
</template>

<script>
import Spin from "../components/Spin";
import axios from "axios";
import Post from "../components/Blog/Post";

export default {
  components: {
    Spin,
    Post,
  },
  data: () => ({
    loading: true,
    post: [],
    not_found: false,
  }),
  mounted() {
    if(this.$route.params.name !== undefined){
      this.loadPost(this.$route.params.name);
    }
    else{
      this.loadPost("ALL");
    }
  },
  methods: {
    loadPost(name) {
      axios
        .get("/api/posts?ios_code=" + name)
        .then((res) => {
          if (res.data.success) {
            this.post = res.data.report;
          } else {
            this.not_found = true;
          }
          setTimeout(() => {
            this.loading = false;
          }, 300);
        })
        .catch((err) => {
          this.not_found = true;
        });
    },
  },
};
</script>

<style scoped>
@import "~bootstrap/dist/css/bootstrap.css";
</style>

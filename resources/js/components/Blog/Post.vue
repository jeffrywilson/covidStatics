<template>
  <div class="col-xs-12 col-sm-6 col-md-1-5 col-lg-1-5">
    <div class="card-flyer">
      <div class="text-box">
        <div class="text-container">
          <h2 v-if="statics">{{ fValue }}</h2>
          <h2 v-else>NULL</h2>
          <p>
            {{ title }}
          </p>
          <h6>{{ date}}</h6>
          
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: "NULL",
    },
    date: {
      type: String,
      default: "NULL",
    },
    statics: {
      type: Number,
      default: "NULL",
    },
  },
  computed: {
    fValue: {
      // getter
      get: function () {
        if (this.statics !== "") {
          return this.formatUSD(this.statics);
        }
      },
    },
  },
  methods: {
    formatUSD(num) {
      return Number(num)
        .toString()
        .replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    },
  },
};
</script>

<style scoped>
.card-flyer {
  border-radius: 5px;
}
card-flyer .image-box {
  background: #ffffff;
  overflow: hidden;
  box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.5);
  border-radius: 5px;
}
.card-flyer .image-box img {
  -webkit-transition: all 0.9s ease;
  -moz-transition: all 0.9s ease;
  -o-transition: all 0.9s ease;
  -ms-transition: all 0.9s ease;
  width: 100%;
  height: 200px;
}
.card-flyer:hover .image-box img {
  opacity: 0.7;
  -webkit-transform: scale(1.15);
  -moz-transform: scale(1.15);
  -ms-transform: scale(1.15);
  -o-transform: scale(1.15);
  transform: scale(1.15);
}
.card-flyer .text-box {
  text-align: center;
}
.card-flyer .text-box .text-container {
  padding: 30px 18px;
}
.card-flyer {
  background: #f7f7f785;
  margin-top: 50px;
  -webkit-transition: all 0.2s ease-in;
  -moz-transition: all 0.2s ease-in;
  -ms-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
  box-shadow: 0px 3px 4px rgba(0, 0, 0, 0.4);
}
.card-flyer:hover {
  background: #fff;
  box-shadow: 0px 15px 26px rgba(0, 0, 0, 0.5);
  -webkit-transition: all 0.2s ease-in;
  -moz-transition: all 0.2s ease-in;
  -ms-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
  margin-top: 50px;
}
.card-flyer .text-box p {
  margin-top: 10px;
  margin-bottom: 0px;
  padding-bottom: 0px;
  font-size: 14px;
  letter-spacing: 1px;
  color: #000000;
}
.card-flyer .text-box h6 {
  margin-top: 6px;
  margin-bottom: 3px;
  font-size: 12px;
  text-transform: uppercase;
  font-family: "Roboto Black", sans-serif;
  letter-spacing: 1px;
  color: #00acc1;
}

.date-card {
  border: 1px solid #ddd;
  width: 100%;

  display: flex;
  align-items: center;
}

.date-card .day {
  font-size: 48px;
  margin: 0px 10px;
  color: #2ab6b6;
}

.date-card .month {
  font-weight: bold;
}

@media (min-width: 992px) {
  .col-md-1-5 {
    width: 20%;
  }
  .col-md-2-5 {
    width: 40%;
  }
  .col-md-3-5 {
    width: 60%;
  }
  .col-md-4-5 {
    width: 80%;
  }
  .col-md-5-5 {
    width: 100%;
  }
}

@media (min-width: 1200px) {
  .col-lg-1-5 {
    width: 20%;
  }
  .col-lg-2-5 {
    width: 40%;
  }
  .col-lg-3-5 {
    width: 60%;
  }
  .col-lg-4-5 {
    width: 80%;
  }
  .col-lg-5-5 {
    width: 100%;
  }
}
</style>

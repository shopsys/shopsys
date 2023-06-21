package performance

import scala.concurrent.duration._
import io.gatling.core.Predef._
import io.gatling.http.Predef._
import io.gatling.jdbc.Predef._

class Stress extends Simulation {
    private val baseUrl = System.getProperty("baseUrl");
    private val authLoginName = System.getProperty("authLoginName");
    private val authPassword = System.getProperty("authPassword");
    private val users = System.getProperty("users").toInt;
    private val duration = System.getProperty("duration").toInt.seconds;

    private val usersProductList = Math.round(users * 0.35).toInt;
    private val usersProductDetail = Math.round(users * 0.25).toInt;
    private val usersHomepage = users - usersProductList - usersProductDetail;

    val httpProtocol = http
        .baseUrl(baseUrl)
        .acceptHeader("text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8")
        .doNotTrackHeader("1")
        .acceptLanguageHeader("en-US,en;q=0.5")
        .acceptEncodingHeader("gzip, deflate")
        .userAgentHeader("Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0")
        .basicAuth(authLoginName, authPassword)

    val headersAjax = Map("X-Requested-With" -> "XMLHttpRequest")

    val scnProductList = scenario("Product List")
        .exec(
            http(f"Product list - $usersProductList%s")
            .get("/47-lg-47la790v-fhd")
        )

    val productsFeeder = csv("products.csv").random
    val scnProductDetail = scenario("Product detail")
        .feed(productsFeeder)
        .exec(
            http(f"Product detail - $usersProductDetail%s")
            .get("${url}")
        )

    val scnHomepage = scenario("Homepage")
        .exec(
            http(f"Homepage - $usersHomepage%s")
            .get("/")
        )

    setUp(
      scnProductList
        .inject(constantConcurrentUsers(usersProductList) during (duration))
        .protocols(httpProtocol),
      scnProductDetail
        .inject(constantConcurrentUsers(usersProductDetail) during (duration))
        .protocols(httpProtocol),
      scnHomepage
        .inject(constantConcurrentUsers(usersHomepage) during (duration))
        .protocols(httpProtocol)
    )
}

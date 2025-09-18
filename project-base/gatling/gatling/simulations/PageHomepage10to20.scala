package performance

import scala.concurrent.duration._
import io.gatling.core.Predef._
import io.gatling.http.Predef._
import io.gatling.jdbc.Predef._

class PageHomepage10to20 extends Simulation {
    private val baseUrl = System.getProperty("baseUrl");
    private val authLoginName = System.getProperty("authLoginName");
    private val authPassword = System.getProperty("authPassword");

    val httpProtocol = http
        .baseUrl(baseUrl)
        .acceptHeader("text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8")
        .doNotTrackHeader("1")
        .acceptLanguageHeader("en-US,en;q=0.5")
        .acceptEncodingHeader("gzip, deflate")
        .userAgentHeader("Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0")
        .basicAuth(authLoginName, authPassword)

    val scn = scenario("Homepage 10→20 Users")
        .exec(http("homepage_10_to_20")
        .get("/"))

    setUp(
      scn.inject(
        rampUsers(10) during(20.seconds),           // Ramp up to 10 users over 20s
        constantUsersPerSec(20) during(25.seconds), // Stay at 20 users for 25s
        rampUsers(0) during(15.seconds)             // Ramp down to 0 over 15s
      ).protocols(httpProtocol)
    )
}
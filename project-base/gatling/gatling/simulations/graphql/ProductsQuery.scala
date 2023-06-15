package performance

import scala.concurrent.duration._
import io.gatling.core.Predef._
import io.gatling.http.Predef._
import io.gatling.jdbc.Predef._

class ProductsQuery extends Simulation {
    private val baseUrl = System.getProperty("baseUrl");
    private val authLoginName = System.getProperty("authLoginName");
    private val authPassword = System.getProperty("authPassword");
    private val users = System.getProperty("users");
    private val duration = System.getProperty("duration");

    val httpProtocol = http
        .baseUrl(baseUrl)
        .acceptHeader("*/*")
        .doNotTrackHeader("1")
        .acceptLanguageHeader("en-US,en;q=0.5")
        .acceptEncodingHeader("gzip, deflate, br")
        .userAgentHeader("Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0")
        .basicAuth(authLoginName, authPassword)

    val scn = scenario("ProductsQuery")
        .exec(
            http(f"page__$users%s__products_query")
                .post("graphql/")
                .header("Content-Type","application/graphql")
                .body(RawFileBody("productsQuery.graphql"))
            )

    setUp(
      scn.inject(
        constantConcurrentUsers(users.toInt) during (duration.toInt.seconds),
      ).protocols(httpProtocol)
    )
}
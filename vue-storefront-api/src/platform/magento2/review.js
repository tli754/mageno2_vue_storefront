import AbstractReviewProxy from '../abstract/review'
import { multiStoreConfig } from './util'

class ReviewProxy extends AbstractReviewProxy {
  constructor (config, req) {
    const Magento2Client = require('./magento2-rest-client/index').Magento2Client;
    super(config, req)
    this.api = Magento2Client(multiStoreConfig(config.magento2.api, req));
  }

  create (reviewData) {
    reviewData.entity_pk_value = reviewData.product_id;
    delete reviewData.product_id;
    return this.api.reviews.create(reviewData);
  }
}

module.exports = ReviewProxy

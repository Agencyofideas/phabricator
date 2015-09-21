<?php

final class DrydockWorkingCopyBlueprintImplementation
  extends DrydockBlueprintImplementation {

  public function isEnabled() {
    return true;
  }

  public function getBlueprintName() {
    return pht('Working Copy');
  }

  public function getDescription() {
    return pht('Allows Drydock to check out working copies of repositories.');
  }

  public function canAnyBlueprintEverAllocateResourceForLease(
    DrydockLease $lease) {
    // TODO: These checks are out of date.
    return true;
  }

  public function canEverAllocateResourceForLease(
    DrydockBlueprint $blueprint,
    DrydockLease $lease) {
    // TODO: These checks are out of date.
    return true;
  }

  public function canAllocateResourceForLease(
    DrydockBlueprint $blueprint,
    DrydockLease $lease) {
    // TODO: These checks are out of date.
    return true;
  }

  public function canAcquireLeaseOnResource(
    DrydockBlueprint $blueprint,
    DrydockResource $resource,
    DrydockLease $lease) {
    // TODO: These checks are out of date.

    $resource_repo = $resource->getAttribute('repositoryID');
    $lease_repo = $lease->getAttribute('repositoryID');

    return ($resource_repo && $lease_repo && ($resource_repo == $lease_repo));
  }

  public function allocateResource(
    DrydockBlueprint $blueprint,
    DrydockLease $lease) {

    $repository_id = $lease->getAttribute('repositoryID');
    if (!$repository_id) {
      throw new Exception(
        pht(
          "Lease is missing required '%s' attribute.",
          'repositoryID'));
    }

    $repository = id(new PhabricatorRepositoryQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withIDs(array($repository_id))
      ->executeOne();

    if (!$repository) {
      throw new Exception(
        pht(
          "Repository '%s' does not exist!",
          $repository_id));
    }

    switch ($repository->getVersionControlSystem()) {
      case PhabricatorRepositoryType::REPOSITORY_TYPE_GIT:
        break;
      default:
        throw new Exception(pht('Unsupported VCS!'));
    }

    // TODO: Policy stuff here too.
    $host_lease = id(new DrydockLease())
      ->setResourceType('host')
      ->waitUntilActive();

    $path = $host_lease->getAttribute('path').$repository->getCallsign();

    $this->log(
      pht('Cloning %s into %s....', $repository->getCallsign(), $path));

    $cmd = $host_lease->getInterface('command');
    $cmd->execx(
      'git clone --origin origin %P %s',
      $repository->getRemoteURIEnvelope(),
      $path);

    $this->log(pht('Complete.'));

    $resource = $this->newResourceTemplate(
      $blueprint,
      pht(
        'Working Copy (%s)',
        $repository->getCallsign()));
    $resource->setStatus(DrydockResourceStatus::STATUS_OPEN);
    $resource->setAttribute('lease.host', $host_lease->getID());
    $resource->setAttribute('path', $path);
    $resource->setAttribute('repositoryID', $repository->getID());
    $resource->save();

    return $resource;
  }

  public function acquireLease(
    DrydockBlueprint $blueprint,
    DrydockResource $resource,
    DrydockLease $lease) {
    return;
  }

  public function getType() {
    return 'working-copy';
  }

  public function getInterface(
    DrydockBlueprint $blueprint,
    DrydockResource $resource,
    DrydockLease $lease,
    $type) {
    // TODO: This blueprint doesn't work at all.
  }

}

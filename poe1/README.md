# POE1

Minimal Path of Exile 1 reference project: cloned open-source tools, documentation, and build guides.

## Structure

```
docs/           Documentation
repos/          Cloned POE tools
POE1Builds/     Build guides
```

## Quick Links

- [Path of Building overview](docs/path-of-building.md)
- [Cloned repos](repos/README.md)
- [Build index](POE1Builds/README.md)

## Build Documents

### League starters

| Build | Guide |
|-------|-------|
| Poisonous Concoction Pathfinder | [POE1Builds/01-poisonous-concoction-pathfinder.md](POE1Builds/01-poisonous-concoction-pathfinder.md) |
| Toxic Rain Pathfinder | [POE1Builds/02-toxic-rain-pathfinder.md](POE1Builds/02-toxic-rain-pathfinder.md) |
| Bone Shatter Juggernaut | [POE1Builds/03-bone-shatter-juggernaut.md](POE1Builds/03-bone-shatter-juggernaut.md) |

### Endgame (community PoB DPS)

| Build | Guide |
|-------|-------|
| Minion Pact Spark Gladiator | [POE1Builds/04-minion-pact-spark-gladiator.md](POE1Builds/04-minion-pact-spark-gladiator.md) |
| Animate Guardian of Smiting | [POE1Builds/05-animate-guardian-of-smiting.md](POE1Builds/05-animate-guardian-of-smiting.md) |
| Detonate Dead Chain Reaction | [POE1Builds/06-detonate-dead-chain-reaction-necro.md](POE1Builds/06-detonate-dead-chain-reaction-necro.md) |

### Data-driven (from Path of Building game files)

| Build | Guide |
|-------|-------|
| Cold Snap of Power Elementalist | [POE1Builds/07-cold-snap-of-power-elementalist.md](POE1Builds/07-cold-snap-of-power-elementalist.md) |
| RF of Arcane Devotion Hierophant | [POE1Builds/08-righteous-fire-arcane-devotion-hierophant.md](POE1Builds/08-righteous-fire-arcane-devotion-hierophant.md) |
| Lightning Strike of **Arcing** Raider | [POE1Builds/09-lightning-strike-of-arcing-raider.md](POE1Builds/09-lightning-strike-of-arcing-raider.md) |

> **Arcing, not “blocked”:** The latest data-driven build uses the transfigured gem **Lightning Strike of Arcing**. *Arcing* means lightning jumps between enemies as chained projectiles (+10% more damage per chain in PoB data). It is not related to attack or spell **block** (the defensive stat).

## Re-clone Tools

```bash
cd repos
git clone --depth 1 https://github.com/PathOfBuildingCommunity/PathOfBuilding.git path-of-building
git clone --depth 1 https://github.com/infernumx/poe_ninja_client.git poe-ninja-client
git clone --depth 1 https://github.com/ayberkgezer/poe-api-manager.git poe-api-manager
git clone --depth 1 https://github.com/KeshHere/POE-Timeless-Jewel-Finder.git timeless-jewel-finder
```
